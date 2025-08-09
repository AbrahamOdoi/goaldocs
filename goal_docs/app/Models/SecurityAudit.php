<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'description',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get audits by action type
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get audits by resource type
     */
    public function scopeByResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }

    /**
     * Scope to get audits by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get recent audits
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>', now()->subDays($days));
    }

    /**
     * Scope to get failed access attempts
     */
    public function scopeFailedAccess($query)
    {
        return $query->where('action', 'access_denied');
    }

    /**
     * Scope to get security events
     */
    public function scopeSecurityEvents($query)
    {
        return $query->whereIn('action', [
            'file_encrypt',
            'file_decrypt',
            'file_watermark',
            'access_denied',
            'permission_violation',
        ]);
    }

    /**
     * Get action display name
     */
    public function getActionDisplayAttribute(): string
    {
        $actions = [
            'file_encrypt' => 'File Encrypted',
            'file_decrypt' => 'File Decrypted',
            'file_watermark' => 'File Watermarked',
            'file_access' => 'File Accessed',
            'access_denied' => 'Access Denied',
            'permission_violation' => 'Permission Violation',
            'login_success' => 'Login Success',
            'login_failed' => 'Login Failed',
            'password_change' => 'Password Changed',
            'account_locked' => 'Account Locked',
        ];

        return $actions[$this->action] ?? ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Get severity level
     */
    public function getSeverityAttribute(): string
    {
        $highSeverity = ['access_denied', 'permission_violation', 'account_locked'];
        $mediumSeverity = ['file_encrypt', 'file_decrypt', 'password_change'];
        $lowSeverity = ['file_access', 'login_success', 'file_watermark'];

        if (in_array($this->action, $highSeverity)) {
            return 'high';
        } elseif (in_array($this->action, $mediumSeverity)) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get severity color
     */
    public function getSeverityColorAttribute(): string
    {
        $colors = [
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
        ];

        return $colors[$this->severity] ?? 'secondary';
    }

    /**
     * Check if audit is recent (within last 24 hours)
     */
    public function getIsRecentAttribute(): bool
    {
        return $this->created_at->isAfter(now()->subDay());
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimestampAttribute(): string
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    /**
     * Get location from IP address (placeholder)
     */
    public function getLocationAttribute(): string
    {
        // In a real implementation, you would use a geolocation service
        return 'Unknown';
    }

    /**
     * Get browser information from user agent
     */
    public function getBrowserInfoAttribute(): array
    {
        $userAgent = $this->user_agent;
        
        // Simple browser detection
        $browser = 'Unknown';
        $os = 'Unknown';
        
        if (strpos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        }
        
        if (strpos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $os = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false) {
            $os = 'iOS';
        }
        
        return [
            'browser' => $browser,
            'os' => $os,
            'user_agent' => $userAgent,
        ];
    }
}
