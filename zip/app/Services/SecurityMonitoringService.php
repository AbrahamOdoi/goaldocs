<?php

namespace App\Services;

use App\Models\SecurityLog;
use App\Models\AuditLog;
use App\Models\UserSession;
use App\Models\SecurityPolicy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityMonitoringService
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Get real-time security monitoring data.
     */
    public function getRealTimeMonitoringData(): array
    {
        $now = Carbon::now();
        $lastHour = $now->subHour();

        // Get recent security events
        $recentSecurityEvents = SecurityLog::where('created_at', '>=', $lastHour)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // Get active sessions
        $activeSessions = UserSession::where('is_active', true)
            ->where('expires_at', '>', $now)
            ->get();

        // Get suspicious activities
        $suspiciousActivities = SecurityLog::where('is_suspicious', true)
            ->where('created_at', '>=', $lastHour)
            ->get();

        // Get failed login attempts
        $failedLogins = SecurityLog::where('event_type', SecurityLog::EVENT_LOGIN_FAILED)
            ->where('created_at', '>=', $lastHour)
            ->get();

        // Get unauthorized access attempts
        $unauthorizedAccess = SecurityLog::where('event_type', SecurityLog::EVENT_UNAUTHORIZED_ACCESS)
            ->where('created_at', '>=', $lastHour)
            ->get();

        return [
            'recent_events' => $recentSecurityEvents,
            'active_sessions' => $activeSessions,
            'suspicious_activities' => $suspiciousActivities,
            'failed_logins' => $failedLogins,
            'unauthorized_access' => $unauthorizedAccess,
            'monitoring_stats' => $this->getMonitoringStatistics($lastHour),
        ];
    }

    /**
     * Get monitoring statistics.
     */
    private function getMonitoringStatistics($since): array
    {
        $totalEvents = SecurityLog::where('created_at', '>=', $since)->count();
        $criticalEvents = SecurityLog::where('created_at', '>=', $since)
            ->where('severity', SecurityLog::SEVERITY_CRITICAL)
            ->count();
        $highEvents = SecurityLog::where('created_at', '>=', $since)
            ->where('severity', SecurityLog::SEVERITY_HIGH)
            ->count();
        $suspiciousEvents = SecurityLog::where('created_at', '>=', $since)
            ->where('is_suspicious', true)
            ->count();

        return [
            'total_events' => $totalEvents,
            'critical_events' => $criticalEvents,
            'high_events' => $highEvents,
            'suspicious_events' => $suspiciousEvents,
            'security_score' => $this->calculateSecurityScore($since),
        ];
    }

    /**
     * Calculate security score.
     */
    private function calculateSecurityScore($since): float
    {
        $totalEvents = SecurityLog::where('created_at', '>=', $since)->count();
        if ($totalEvents === 0) return 100.0;

        $criticalEvents = SecurityLog::where('created_at', '>=', $since)
            ->where('severity', SecurityLog::SEVERITY_CRITICAL)
            ->count();
        $highEvents = SecurityLog::where('created_at', '>=', $since)
            ->where('severity', SecurityLog::SEVERITY_HIGH)
            ->count();

        $penalty = ($criticalEvents * 10) + ($highEvents * 5);
        return max(0, 100 - $penalty);
    }

    /**
     * Detect security threats in real-time.
     */
    public function detectThreats(): array
    {
        $threats = [];
        $now = Carbon::now();
        $lastHour = $now->subHour();

        // Check for brute force attacks
        $failedLogins = SecurityLog::where('event_type', SecurityLog::EVENT_LOGIN_FAILED)
            ->where('created_at', '>=', $lastHour)
            ->get()
            ->groupBy('user_id');

        foreach ($failedLogins as $userId => $attempts) {
            if ($attempts->count() >= 5) {
                $threats[] = [
                    'type' => 'brute_force_attack',
                    'severity' => 'high',
                    'description' => 'Multiple failed login attempts detected',
                    'user_id' => $userId,
                    'attempts' => $attempts->count(),
                    'detected_at' => $now->toISOString(),
                ];
            }
        }

        // Check for suspicious IP addresses
        $suspiciousIPs = SecurityLog::where('is_suspicious', true)
            ->where('created_at', '>=', $lastHour)
            ->get()
            ->groupBy('ip_address');

        foreach ($suspiciousIPs as $ip => $events) {
            if ($events->count() >= 3) {
                $threats[] = [
                    'type' => 'suspicious_ip',
                    'severity' => 'medium',
                    'description' => 'Suspicious activity from IP address',
                    'ip_address' => $ip,
                    'events' => $events->count(),
                    'detected_at' => $now->toISOString(),
                ];
            }
        }

        // Check for unusual access patterns
        $unusualAccess = $this->detectUnusualAccessPatterns($lastHour);
        $threats = array_merge($threats, $unusualAccess);

        return $threats;
    }

    /**
     * Detect unusual access patterns.
     */
    private function detectUnusualAccessPatterns($since): array
    {
        $threats = [];

        // Check for access outside business hours
        $afterHoursAccess = SecurityLog::where('created_at', '>=', $since)
            ->where('event_type', SecurityLog::EVENT_LOGIN_SUCCESS)
            ->get()
            ->filter(function ($log) {
                $hour = Carbon::parse($log->created_at)->hour;
                return $hour < 6 || $hour > 22; // Outside 6 AM - 10 PM
            });

        if ($afterHoursAccess->count() > 0) {
            $threats[] = [
                'type' => 'after_hours_access',
                'severity' => 'medium',
                'description' => 'Access detected outside business hours',
                'count' => $afterHoursAccess->count(),
                'detected_at' => Carbon::now()->toISOString(),
            ];
        }

        // Check for multiple concurrent sessions
        $concurrentSessions = UserSession::where('is_active', true)
            ->get()
            ->groupBy('user_id')
            ->filter(function ($sessions) {
                return $sessions->count() > 3;
            });

        foreach ($concurrentSessions as $userId => $sessions) {
            $threats[] = [
                'type' => 'multiple_concurrent_sessions',
                'severity' => 'medium',
                'description' => 'Multiple concurrent sessions detected',
                'user_id' => $userId,
                'session_count' => $sessions->count(),
                'detected_at' => Carbon::now()->toISOString(),
            ];
        }

        return $threats;
    }

    /**
     * Generate security alerts.
     */
    public function generateSecurityAlerts(): array
    {
        $alerts = [];
        $threats = $this->detectThreats();

        foreach ($threats as $threat) {
            $alert = [
                'id' => 'ALERT-' . time() . '-' . uniqid(),
                'type' => $threat['type'],
                'severity' => $threat['severity'],
                'title' => $this->getAlertTitle($threat['type']),
                'description' => $threat['description'],
                'details' => $threat,
                'created_at' => Carbon::now()->toISOString(),
                'status' => 'active',
                'requires_action' => $threat['severity'] === 'high' || $threat['severity'] === 'critical',
            ];

            $alerts[] = $alert;

            // Log the alert
            $this->securityService->logSecurityEvent(
                $threat['user_id'] ?? null,
                SecurityLog::EVENT_SECURITY_ALERT,
                SecurityLog::SEVERITY_HIGH,
                "Security alert: {$alert['title']}",
                [
                    'alert_id' => $alert['id'],
                    'threat_type' => $threat['type'],
                    'severity' => $threat['severity'],
                ]
            );
        }

        return $alerts;
    }

    /**
     * Get alert title based on threat type.
     */
    private function getAlertTitle($threatType): string
    {
        $titles = [
            'brute_force_attack' => 'Brute Force Attack Detected',
            'suspicious_ip' => 'Suspicious IP Activity',
            'after_hours_access' => 'After Hours Access Detected',
            'multiple_concurrent_sessions' => 'Multiple Concurrent Sessions',
            'unauthorized_access' => 'Unauthorized Access Attempt',
        ];

        return $titles[$threatType] ?? 'Security Threat Detected';
    }

    /**
     * Handle incident response.
     */
    public function handleIncidentResponse($alertId, $action, $notes = ''): array
    {
        $alert = $this->getAlertById($alertId);
        if (!$alert) {
            return ['success' => false, 'message' => 'Alert not found'];
        }

        $response = [
            'alert_id' => $alertId,
            'action' => $action,
            'notes' => $notes,
            'responded_at' => Carbon::now()->toISOString(),
            'responded_by' => auth()->id(),
        ];

        switch ($action) {
            case 'acknowledge':
                $response['status'] = 'acknowledged';
                break;
            case 'investigate':
                $response['status'] = 'under_investigation';
                break;
            case 'resolve':
                $response['status'] = 'resolved';
                break;
            case 'escalate':
                $response['status'] = 'escalated';
                break;
            default:
                $response['status'] = 'pending';
        }

        // Log the incident response
        $this->securityService->logSecurityEvent(
            auth()->id(),
            SecurityLog::EVENT_INCIDENT_RESPONSE,
            SecurityLog::SEVERITY_MEDIUM,
            "Incident response: {$action}",
            $response
        );

        return [
            'success' => true,
            'message' => 'Incident response recorded successfully',
            'response' => $response,
        ];
    }

    /**
     * Get alert by ID.
     */
    private function getAlertById($alertId): ?array
    {
        // This would typically come from a database
        // For now, we'll return a mock alert
        return [
            'id' => $alertId,
            'type' => 'security_threat',
            'severity' => 'high',
            'title' => 'Security Threat Detected',
            'description' => 'A security threat has been detected',
            'status' => 'active',
        ];
    }

    /**
     * Get security monitoring statistics.
     */
    public function getSecurityMonitoringStatistics(): array
    {
        $now = Carbon::now();
        $last24Hours = $now->subDay();
        $last7Days = $now->subWeek();
        $last30Days = $now->subMonth();

        return [
            'last_24_hours' => [
                'total_events' => SecurityLog::where('created_at', '>=', $last24Hours)->count(),
                'critical_events' => SecurityLog::where('created_at', '>=', $last24Hours)
                    ->where('severity', SecurityLog::SEVERITY_CRITICAL)->count(),
                'high_events' => SecurityLog::where('created_at', '>=', $last24Hours)
                    ->where('severity', SecurityLog::SEVERITY_HIGH)->count(),
                'suspicious_events' => SecurityLog::where('created_at', '>=', $last24Hours)
                    ->where('is_suspicious', true)->count(),
                'failed_logins' => SecurityLog::where('created_at', '>=', $last24Hours)
                    ->where('event_type', SecurityLog::EVENT_LOGIN_FAILED)->count(),
            ],
            'last_7_days' => [
                'total_events' => SecurityLog::where('created_at', '>=', $last7Days)->count(),
                'critical_events' => SecurityLog::where('created_at', '>=', $last7Days)
                    ->where('severity', SecurityLog::SEVERITY_CRITICAL)->count(),
                'high_events' => SecurityLog::where('created_at', '>=', $last7Days)
                    ->where('severity', SecurityLog::SEVERITY_HIGH)->count(),
                'suspicious_events' => SecurityLog::where('created_at', '>=', $last7Days)
                    ->where('is_suspicious', true)->count(),
                'failed_logins' => SecurityLog::where('created_at', '>=', $last7Days)
                    ->where('event_type', SecurityLog::EVENT_LOGIN_FAILED)->count(),
            ],
            'last_30_days' => [
                'total_events' => SecurityLog::where('created_at', '>=', $last30Days)->count(),
                'critical_events' => SecurityLog::where('created_at', '>=', $last30Days)
                    ->where('severity', SecurityLog::SEVERITY_CRITICAL)->count(),
                'high_events' => SecurityLog::where('created_at', '>=', $last30Days)
                    ->where('severity', SecurityLog::SEVERITY_HIGH)->count(),
                'suspicious_events' => SecurityLog::where('created_at', '>=', $last30Days)
                    ->where('is_suspicious', true)->count(),
                'failed_logins' => SecurityLog::where('created_at', '>=', $last30Days)
                    ->where('event_type', SecurityLog::EVENT_LOGIN_FAILED)->count(),
            ],
            'active_threats' => count($this->detectThreats()),
            'security_score' => $this->calculateSecurityScore($last24Hours),
        ];
    }

    /**
     * Get vulnerability assessment.
     */
    public function getVulnerabilityAssessment(): array
    {
        $assessment = [
            'overall_score' => 0,
            'vulnerabilities' => [],
            'recommendations' => [],
        ];

        $score = 100;

        // Check for weak passwords
        $weakPasswords = $this->checkWeakPasswords();
        if ($weakPasswords > 0) {
            $assessment['vulnerabilities'][] = [
                'type' => 'weak_passwords',
                'severity' => 'high',
                'description' => "{$weakPasswords} users have weak passwords",
                'recommendation' => 'Enforce strong password policies',
            ];
            $score -= 20;
        }

        // Check for inactive security policies
        $inactivePolicies = SecurityPolicy::where('is_active', false)->count();
        if ($inactivePolicies > 0) {
            $assessment['vulnerabilities'][] = [
                'type' => 'inactive_policies',
                'severity' => 'medium',
                'description' => "{$inactivePolicies} security policies are inactive",
                'recommendation' => 'Review and activate necessary security policies',
            ];
            $score -= 10;
        }

        // Check for expired sessions
        $expiredSessions = UserSession::where('expires_at', '<', Carbon::now())
            ->where('is_active', true)
            ->count();
        if ($expiredSessions > 0) {
            $assessment['vulnerabilities'][] = [
                'type' => 'expired_sessions',
                'severity' => 'medium',
                'description' => "{$expiredSessions} sessions have expired but are still active",
                'recommendation' => 'Clean up expired sessions',
            ];
            $score -= 15;
        }

        // Check for MFA usage
        $usersWithoutMFA = UserSession::where('is_mfa_verified', false)
            ->where('is_active', true)
            ->distinct('user_id')
            ->count();
        if ($usersWithoutMFA > 0) {
            $assessment['vulnerabilities'][] = [
                'type' => 'no_mfa',
                'severity' => 'high',
                'description' => "{$usersWithoutMFA} users are not using MFA",
                'recommendation' => 'Enforce MFA for all users',
            ];
            $score -= 25;
        }

        $assessment['overall_score'] = max(0, $score);

        // Generate recommendations
        $assessment['recommendations'] = $this->generateSecurityRecommendations($assessment['vulnerabilities']);

        return $assessment;
    }

    /**
     * Check for weak passwords.
     */
    private function checkWeakPasswords(): int
    {
        // This would typically check against password policies
        // For now, return a mock value
        return 0;
    }

    /**
     * Generate security recommendations.
     */
    private function generateSecurityRecommendations($vulnerabilities): array
    {
        $recommendations = [];

        foreach ($vulnerabilities as $vulnerability) {
            $recommendations[] = [
                'priority' => $vulnerability['severity'] === 'high' ? 'high' : 'medium',
                'title' => "Fix {$vulnerability['type']}",
                'description' => $vulnerability['recommendation'],
                'vulnerability_type' => $vulnerability['type'],
            ];
        }

        return $recommendations;
    }

    /**
     * Get security monitoring dashboard data.
     */
    public function getSecurityMonitoringDashboardData(): array
    {
        return [
            'real_time_data' => $this->getRealTimeMonitoringData(),
            'threats' => $this->detectThreats(),
            'alerts' => $this->generateSecurityAlerts(),
            'statistics' => $this->getSecurityMonitoringStatistics(),
            'vulnerability_assessment' => $this->getVulnerabilityAssessment(),
        ];
    }
}
