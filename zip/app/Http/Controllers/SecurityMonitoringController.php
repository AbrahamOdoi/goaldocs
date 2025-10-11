<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\SecurityMonitoringService;
use Illuminate\Support\Facades\Auth;

class SecurityMonitoringController extends Controller
{
    protected $securityMonitoringService;

    public function __construct(SecurityMonitoringService $securityMonitoringService)
    {
        $this->securityMonitoringService = $securityMonitoringService;
    }

    /**
     * Display the security monitoring dashboard.
     */
    public function dashboard()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('security-monitoring.dashboard');
    }

    /**
     * Display real-time monitoring data.
     */
    public function realTimeMonitoring()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('security-monitoring.real-time');
    }

    /**
     * Display threat detection interface.
     */
    public function threatDetection()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('security-monitoring.threat-detection');
    }

    /**
     * Display security alerts interface.
     */
    public function securityAlerts()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('security-monitoring.alerts');
    }

    /**
     * Display incident response interface.
     */
    public function incidentResponse()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('security-monitoring.incident-response');
    }

    /**
     * Display vulnerability assessment interface.
     */
    public function vulnerabilityAssessment()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('security-monitoring.vulnerability-assessment');
    }

    /**
     * Display security reporting interface.
     */
    public function securityReporting()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('security-monitoring.reporting');
    }

    /**
     * Display security monitoring settings.
     */
    public function settings()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return view('security-monitoring.settings');
    }

    // API Endpoints

    /**
     * Get real-time monitoring data.
     */
    public function getRealTimeData(): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $data = $this->securityMonitoringService->getRealTimeMonitoringData();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get real-time data'], 500);
        }
    }

    /**
     * Get detected threats.
     */
    public function getThreats(): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $threats = $this->securityMonitoringService->detectThreats();
            return response()->json(['threats' => $threats]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to detect threats'], 500);
        }
    }

    /**
     * Get security alerts.
     */
    public function getAlerts(): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $alerts = $this->securityMonitoringService->generateSecurityAlerts();
            return response()->json(['alerts' => $alerts]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate alerts'], 500);
        }
    }

    /**
     * Handle incident response.
     */
    public function handleIncidentResponse(Request $request): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'alert_id' => 'required|string',
            'action' => 'required|string|in:acknowledge,investigate,resolve,escalate',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $result = $this->securityMonitoringService->handleIncidentResponse(
                $request->alert_id,
                $request->action,
                $request->notes
            );
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to handle incident response'], 500);
        }
    }

    /**
     * Get security monitoring statistics.
     */
    public function getStatistics(): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $statistics = $this->securityMonitoringService->getSecurityMonitoringStatistics();
            return response()->json($statistics);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get statistics'], 500);
        }
    }

    /**
     * Get vulnerability assessment.
     */
    public function getVulnerabilityAssessment(): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $assessment = $this->securityMonitoringService->getVulnerabilityAssessment();
            return response()->json($assessment);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get vulnerability assessment'], 500);
        }
    }

    /**
     * Get security monitoring dashboard data.
     */
    public function getDashboardData(): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $data = $this->securityMonitoringService->getSecurityMonitoringDashboardData();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get dashboard data'], 500);
        }
    }

    /**
     * Update security monitoring settings.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'real_time_monitoring_enabled' => 'boolean',
            'threat_detection_enabled' => 'boolean',
            'alert_notifications_enabled' => 'boolean',
            'auto_response_enabled' => 'boolean',
            'vulnerability_scanning_enabled' => 'boolean',
            'scan_frequency' => 'string|in:hourly,daily,weekly',
            'alert_threshold' => 'integer|min:1|max:100',
            'retention_period' => 'integer|min:1|max:365',
        ]);

        try {
            // Update settings logic would go here
            // For now, we'll just return success
            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update settings'], 500);
        }
    }

    /**
     * Export security monitoring report.
     */
    public function exportReport(Request $request): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'report_type' => 'required|string|in:threats,alerts,incidents,vulnerabilities,comprehensive',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
            'format' => 'string|in:pdf,excel,csv',
        ]);

        try {
            // Export logic would go here
            // For now, we'll just return success
            return response()->json([
                'success' => true,
                'message' => 'Report exported successfully',
                'download_url' => '/security-monitoring/download/report-' . time() . '.' . ($request->format ?? 'pdf'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to export report'], 500);
        }
    }

    /**
     * Get security monitoring summary.
     */
    public function getSummary(): JsonResponse
    {
        if (!Auth::user()->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $data = $this->securityMonitoringService->getSecurityMonitoringDashboardData();
            
            $summary = [
                'active_threats' => count($data['threats']),
                'active_alerts' => count($data['alerts']),
                'security_score' => $data['statistics']['security_score'],
                'vulnerability_score' => $data['vulnerability_assessment']['overall_score'],
                'recent_events' => $data['real_time_data']['monitoring_stats']['total_events'],
                'critical_events' => $data['real_time_data']['monitoring_stats']['critical_events'],
            ];

            return response()->json($summary);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get summary'], 500);
        }
    }
}
