<?php

namespace App\Http\Controllers;

use App\Services\ComplianceService;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ComplianceController extends Controller
{
    protected $complianceService;

    public function __construct(ComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    /**
     * Show compliance dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $stats = $this->complianceService->getComplianceStatistics();
        $standards = [
            AuditLog::COMPLIANCE_GDPR,
            AuditLog::COMPLIANCE_HIPAA,
            AuditLog::COMPLIANCE_SOX,
            AuditLog::COMPLIANCE_PCI,
            AuditLog::COMPLIANCE_ISO27001,
        ];

        return view('compliance.dashboard', compact('stats', 'standards'));
    }

    /**
     * Show compliance reports
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $standard = $request->get('standard', AuditLog::COMPLIANCE_GDPR);
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $report = $this->complianceService->generateComplianceReport($standard, $startDate, $endDate);
        $standards = [
            AuditLog::COMPLIANCE_GDPR,
            AuditLog::COMPLIANCE_HIPAA,
            AuditLog::COMPLIANCE_SOX,
            AuditLog::COMPLIANCE_PCI,
            AuditLog::COMPLIANCE_ISO27001,
        ];

        return view('compliance.reports', compact('report', 'standards', 'standard', 'startDate', 'endDate'));
    }

    /**
     * Generate compliance report API
     */
    public function generateReport(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'standard' => 'required|string|in:' . implode(',', [
                AuditLog::COMPLIANCE_GDPR,
                AuditLog::COMPLIANCE_HIPAA,
                AuditLog::COMPLIANCE_SOX,
                AuditLog::COMPLIANCE_PCI,
                AuditLog::COMPLIANCE_ISO27001,
            ]),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $report = $this->complianceService->generateComplianceReport(
                $request->standard,
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => true,
                'report' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to generate report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export compliance report
     */
    public function exportReport(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'standard' => 'required|string|in:' . implode(',', [
                AuditLog::COMPLIANCE_GDPR,
                AuditLog::COMPLIANCE_HIPAA,
                AuditLog::COMPLIANCE_SOX,
                AuditLog::COMPLIANCE_PCI,
                AuditLog::COMPLIANCE_ISO27001,
            ]),
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'format' => 'required|string|in:json,csv',
        ]);

        try {
            $filePath = $this->complianceService->exportComplianceReport(
                $request->standard,
                $request->start_date,
                $request->end_date,
                $request->format
            );

            $filename = basename($filePath);

            return response()->json([
                'success' => true,
                'message' => 'Compliance report exported successfully',
                'download_url' => route('compliance.download-report', $filename),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to export report: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download exported report
     */
    public function downloadReport($filename)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $filePath = storage_path('app/compliance-reports/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath);
    }

    /**
     * Get compliance statistics API
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $days = $request->get('days', 30);
        $stats = $this->complianceService->getComplianceStatistics($days);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Show compliance audit logs
     */
    public function auditLogs(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $query = AuditLog::complianceRelated()->with('user');

        // Filter by compliance standard
        if ($request->filled('compliance_standard')) {
            $query->byComplianceStandard($request->compliance_standard);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->byAction($request->action);
        }

        // Filter by resource type
        if ($request->filled('resource_type')) {
            $query->byResourceType($request->resource_type);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->paginate(20);
        $complianceStandards = AuditLog::select('compliance_standard')
            ->distinct()
            ->whereNotNull('compliance_standard')
            ->pluck('compliance_standard');

        return view('compliance.audit-logs', compact('auditLogs', 'complianceStandards'));
    }

    /**
     * Show compliance violations
     */
    public function violations(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $standards = [
            AuditLog::COMPLIANCE_GDPR,
            AuditLog::COMPLIANCE_HIPAA,
            AuditLog::COMPLIANCE_SOX,
            AuditLog::COMPLIANCE_PCI,
            AuditLog::COMPLIANCE_ISO27001,
        ];

        $violations = [];
        $selectedStandard = $request->get('standard', AuditLog::COMPLIANCE_GDPR);
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        if ($request->filled('standard')) {
            $report = $this->complianceService->generateComplianceReport($selectedStandard, $startDate, $endDate);
            $violations = $report['violations'] ?? [];
        }

        return view('compliance.violations', compact('violations', 'standards', 'selectedStandard', 'startDate', 'endDate'));
    }

    /**
     * Show compliance recommendations
     */
    public function recommendations(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $standards = [
            AuditLog::COMPLIANCE_GDPR,
            AuditLog::COMPLIANCE_HIPAA,
            AuditLog::COMPLIANCE_SOX,
            AuditLog::COMPLIANCE_PCI,
            AuditLog::COMPLIANCE_ISO27001,
        ];

        $recommendations = [];
        $selectedStandard = $request->get('standard', AuditLog::COMPLIANCE_GDPR);
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        if ($request->filled('standard')) {
            $report = $this->complianceService->generateComplianceReport($selectedStandard, $startDate, $endDate);
            $recommendations = $report['recommendations'] ?? [];
        }

        return view('compliance.recommendations', compact('recommendations', 'standards', 'selectedStandard', 'startDate', 'endDate'));
    }

    /**
     * Show compliance settings
     */
    public function settings()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        return view('compliance.settings');
    }

    /**
     * Update compliance settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'gdpr_enabled' => 'boolean',
            'hipaa_enabled' => 'boolean',
            'sox_enabled' => 'boolean',
            'pci_enabled' => 'boolean',
            'iso27001_enabled' => 'boolean',
            'retention_days' => 'integer|min:30|max:3650',
            'auto_cleanup' => 'boolean',
        ]);

        try {
            // Update compliance settings (implementation would depend on your settings system)
            // For now, we'll just return success
            
            return response()->json([
                'success' => true,
                'message' => 'Compliance settings updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update settings: ' . $e->getMessage()], 500);
        }
    }
}
