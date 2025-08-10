<?php

namespace App\Services;

use App\Models\SecurityLog;
use App\Models\AuditLog;
use App\Models\UserSession;
use App\Models\SecurityPolicy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SecurityService
{
    /**
     * Log a security event.
     */
    public function logSecurityEvent(
        $eventType,
        $description,
        $severity = SecurityLog::SEVERITY_LOW,
        $userId = null,
        $metadata = []
    ): SecurityLog {
        $request = request();
        
        return SecurityLog::create([
            'user_id' => $userId ?? auth()->id(),
            'event_type' => $eventType,
            'severity' => $severity,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'location' => $this->getLocationFromIP($request->ip()),
            'metadata' => $metadata,
            'is_suspicious' => $this->isSuspiciousEvent($eventType, $metadata),
            'requires_review' => $severity === SecurityLog::SEVERITY_HIGH || $severity === SecurityLog::SEVERITY_CRITICAL,
        ]);
    }

    /**
     * Log an audit event.
     */
    public function logAuditEvent(
        $action,
        $resourceType,
        $resourceId = null,
        $resourceName = null,
        $oldValues = null,
        $newValues = null,
        $description = null,
        $userId = null,
        $metadata = []
    ): AuditLog {
        $request = request();
        
        // Determine if this is compliance-related
        $isComplianceRelated = $this->isComplianceRelatedAction($action, $resourceType);
        $complianceStandard = $this->getComplianceStandard($resourceType);
        
        return AuditLog::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'resource_name' => $resourceName,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description ?? $this->generateAuditDescription($action, $resourceType, $resourceName),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
            'is_compliance_related' => $isComplianceRelated,
            'compliance_standard' => $complianceStandard,
        ]);
    }

    /**
     * Create a new user session.
     */
    public function createUserSession(User $user, Request $request): UserSession
    {
        $sessionId = Str::random(64);
        $deviceInfo = $this->parseUserAgent($request->userAgent());
        
        return UserSession::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'location' => $this->getLocationFromIP($request->ip()),
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'os' => $deviceInfo['os'],
            'is_secure' => $request->isSecure(),
            'is_mfa_verified' => false, // Will be updated after MFA verification
            'last_activity' => now(),
            'expires_at' => now()->addMinutes($this->getSessionTimeout($user)),
            'metadata' => [
                'login_time' => now()->toISOString(),
                'device_info' => $deviceInfo,
            ],
        ]);
    }

    /**
     * Update session activity.
     */
    public function updateSessionActivity($sessionId): bool
    {
        $session = UserSession::where('session_id', $sessionId)->first();
        
        if (!$session || !$session->is_active) {
            return false;
        }

        $session->updateLastActivity();
        
        // Extend session if needed
        if ($session->expires_at && $session->expires_at->diffInMinutes(now()) < 30) {
            $session->extend();
        }

        return true;
    }

    /**
     * Deactivate a session.
     */
    public function deactivateSession($sessionId): bool
    {
        $session = UserSession::where('session_id', $sessionId)->first();
        
        if ($session) {
            $session->deactivate();
            return true;
        }

        return false;
    }

    /**
     * Deactivate all sessions for a user.
     */
    public function deactivateAllUserSessions($userId): int
    {
        return UserSession::where('user_id', $userId)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Get active sessions for a user.
     */
    public function getUserActiveSessions($userId): \Illuminate\Database\Eloquent\Collection
    {
        return UserSession::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * Check if user has exceeded maximum concurrent sessions.
     */
    public function hasExceededMaxSessions($userId): bool
    {
        $maxSessions = $this->getMaxConcurrentSessions($userId);
        $activeSessions = UserSession::where('user_id', $userId)
            ->where('is_active', true)
            ->count();

        return $activeSessions >= $maxSessions;
    }

    /**
     * Validate password against policy.
     */
    public function validatePassword($password, $userId = null): array
    {
        $policy = $this->getPasswordPolicy($userId);
        $errors = [];

        if (strlen($password) < $policy['min_length']) {
            $errors[] = "Password must be at least {$policy['min_length']} characters long.";
        }

        if ($policy['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if ($policy['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }

        if ($policy['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number.';
        }

        if ($policy['require_symbols'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Check if password has expired.
     */
    public function isPasswordExpired(User $user): bool
    {
        $policy = $this->getPasswordPolicy($user->id);
        $maxAge = $policy['max_age_days'];
        
        if ($maxAge <= 0) {
            return false; // No expiration
        }

        $lastPasswordChange = $user->password_changed_at ?? $user->created_at;
        return $lastPasswordChange->addDays($maxAge)->isPast();
    }

    /**
     * Get security statistics.
     */
    public function getSecurityStatistics($days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_security_events' => SecurityLog::where('created_at', '>=', $startDate)->count(),
            'suspicious_events' => SecurityLog::suspicious()->where('created_at', '>=', $startDate)->count(),
            'events_requiring_review' => SecurityLog::requiresReview()->where('created_at', '>=', $startDate)->count(),
            'critical_events' => SecurityLog::bySeverity(SecurityLog::SEVERITY_CRITICAL)->where('created_at', '>=', $startDate)->count(),
            'high_events' => SecurityLog::bySeverity(SecurityLog::SEVERITY_HIGH)->where('created_at', '>=', $startDate)->count(),
            'medium_events' => SecurityLog::bySeverity(SecurityLog::SEVERITY_MEDIUM)->where('created_at', '>=', $startDate)->count(),
            'low_events' => SecurityLog::bySeverity(SecurityLog::SEVERITY_LOW)->where('created_at', '>=', $startDate)->count(),
            'total_audit_events' => AuditLog::where('created_at', '>=', $startDate)->count(),
            'compliance_events' => AuditLog::complianceRelated()->where('created_at', '>=', $startDate)->count(),
            'active_sessions' => UserSession::active()->count(),
            'expired_sessions' => UserSession::expired()->count(),
        ];
    }

    /**
     * Get recent security events.
     */
    public function getRecentSecurityEvents($limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return SecurityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent audit events.
     */
    public function getRecentAuditEvents($limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get security events by type.
     */
    public function getSecurityEventsByType($eventType, $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return SecurityLog::with('user')
            ->byEventType($eventType)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit events by resource.
     */
    public function getAuditEventsByResource($resourceType, $resourceId, $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::with('user')
            ->byResource($resourceType, $resourceId)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get password policy for user.
     */
    private function getPasswordPolicy($userId = null): array
    {
        $cacheKey = "password_policy_{$userId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            $policy = SecurityPolicy::active()
                ->currentlyEffective()
                ->byType(SecurityPolicy::TYPE_PASSWORD)
                ->forUser($userId)
                ->first();

            if ($policy) {
                return $policy->getPasswordPolicySettings();
            }

            // Default policy
            return [
                'min_length' => 8,
                'require_uppercase' => true,
                'require_lowercase' => true,
                'require_numbers' => true,
                'require_symbols' => true,
                'max_age_days' => 90,
                'prevent_reuse' => 5,
            ];
        });
    }

    /**
     * Get session timeout for user.
     */
    private function getSessionTimeout($userId): int
    {
        $cacheKey = "session_timeout_{$userId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            $policy = SecurityPolicy::active()
                ->currentlyEffective()
                ->byType(SecurityPolicy::TYPE_SESSION)
                ->forUser($userId)
                ->first();

            if ($policy) {
                return $policy->getSessionPolicySettings()['timeout_minutes'] ?? 120;
            }

            return 120; // Default 2 hours
        });
    }

    /**
     * Get max concurrent sessions for user.
     */
    private function getMaxConcurrentSessions($userId): int
    {
        $cacheKey = "max_sessions_{$userId}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            $policy = SecurityPolicy::active()
                ->currentlyEffective()
                ->byType(SecurityPolicy::TYPE_SESSION)
                ->forUser($userId)
                ->first();

            if ($policy) {
                return $policy->getSessionPolicySettings()['max_concurrent_sessions'] ?? 5;
            }

            return 5; // Default 5 sessions
        });
    }

    /**
     * Parse user agent string.
     */
    private function parseUserAgent($userAgent): array
    {
        $deviceType = 'desktop';
        $browser = 'Unknown';
        $os = 'Unknown';

        // Simple device detection
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            $deviceType = 'mobile';
            if (preg_match('/iPad/', $userAgent)) {
                $deviceType = 'tablet';
            }
        }

        // Browser detection
        if (preg_match('/Chrome/', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge/', $userAgent)) {
            $browser = 'Edge';
        }

        // OS detection
        if (preg_match('/Windows/', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac/', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iPhone|iPad/', $userAgent)) {
            $os = 'iOS';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }

    /**
     * Get location from IP address.
     */
    private function getLocationFromIP($ip): ?string
    {
        // This is a simplified implementation
        // In production, you would use a geolocation service
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            // Public IP - could use a service like MaxMind or IP2Location
            return 'Unknown Location';
        }

        return 'Local Network';
    }

    /**
     * Check if event is suspicious.
     */
    private function isSuspiciousEvent($eventType, $metadata): bool
    {
        $suspiciousPatterns = [
            SecurityLog::EVENT_LOGIN_FAILED => 5, // 5 failed attempts
            SecurityLog::EVENT_FILE_ACCESS => 100, // 100 file accesses in short time
            SecurityLog::EVENT_PERMISSION_CHANGE => 1, // Any permission change
        ];

        if (!isset($suspiciousPatterns[$eventType])) {
            return false;
        }

        $threshold = $suspiciousPatterns[$eventType];
        $recentEvents = SecurityLog::byEventType($eventType)
            ->where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        return $recentEvents >= $threshold;
    }

    /**
     * Check if action is compliance-related.
     */
    private function isComplianceRelatedAction($action, $resourceType): bool
    {
        $complianceActions = [
            AuditLog::ACTION_CREATE,
            AuditLog::ACTION_UPDATE,
            AuditLog::ACTION_DELETE,
            AuditLog::ACTION_EXPORT,
            AuditLog::ACTION_PERMISSION_CHANGE,
        ];

        $complianceResources = [
            AuditLog::RESOURCE_USER,
            AuditLog::RESOURCE_FILE,
            AuditLog::RESOURCE_DOCUMENT,
            AuditLog::RESOURCE_REPORT,
        ];

        return in_array($action, $complianceActions) && in_array($resourceType, $complianceResources);
    }

    /**
     * Get compliance standard for resource type.
     */
    private function getComplianceStandard($resourceType): ?string
    {
        $standards = [
            AuditLog::RESOURCE_USER => AuditLog::COMPLIANCE_GDPR,
            AuditLog::RESOURCE_FILE => AuditLog::COMPLIANCE_ISO27001,
            AuditLog::RESOURCE_DOCUMENT => AuditLog::COMPLIANCE_SOX,
            AuditLog::RESOURCE_REPORT => AuditLog::COMPLIANCE_SOX,
        ];

        return $standards[$resourceType] ?? null;
    }

    /**
     * Generate audit description.
     */
    private function generateAuditDescription($action, $resourceType, $resourceName): string
    {
        $actionLabel = match($action) {
            AuditLog::ACTION_CREATE => 'Created',
            AuditLog::ACTION_READ => 'Viewed',
            AuditLog::ACTION_UPDATE => 'Updated',
            AuditLog::ACTION_DELETE => 'Deleted',
            AuditLog::ACTION_EXPORT => 'Exported',
            AuditLog::ACTION_IMPORT => 'Imported',
            AuditLog::ACTION_LOGIN => 'Logged in',
            AuditLog::ACTION_LOGOUT => 'Logged out',
            AuditLog::ACTION_PERMISSION_CHANGE => 'Changed permissions for',
            default => ucfirst($action),
        };

        $resourceLabel = match($resourceType) {
            AuditLog::RESOURCE_USER => 'user',
            AuditLog::RESOURCE_FILE => 'file',
            AuditLog::RESOURCE_DOCUMENT => 'document',
            AuditLog::RESOURCE_FOLDER => 'folder',
            AuditLog::RESOURCE_REPORT => 'report',
            AuditLog::RESOURCE_SETTING => 'setting',
            AuditLog::RESOURCE_POLICY => 'policy',
            default => $resourceType,
        };

        return $actionLabel . ' ' . $resourceLabel . ($resourceName ? ": {$resourceName}" : '');
    }
} 