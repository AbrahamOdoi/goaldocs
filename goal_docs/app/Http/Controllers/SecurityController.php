<?php

namespace App\Http\Controllers;

use App\Models\SecurityLog;
use App\Models\AuditLog;
use App\Models\UserSession;
use App\Models\SecurityPolicy;
use App\Models\User;
use App\Services\SecurityService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class SecurityController extends Controller
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Show security dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $stats = $this->securityService->getSecurityStatistics();
        $recentSecurityEvents = $this->securityService->getRecentSecurityEvents(10);
        $recentAuditEvents = $this->securityService->getRecentAuditEvents(10);
        $activeSessions = UserSession::active()->with('user')->get();
        $policies = SecurityPolicy::active()->currentlyEffective()->get();
        
        return view('security.dashboard', compact('stats', 'recentSecurityEvents', 'recentAuditEvents', 'activeSessions', 'policies'));
    }

    /**
     * Show security logs
     */
    public function securityLogs(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $query = SecurityLog::with('user');

        // Filter by event type
        if ($request->filled('event_type')) {
            $query->byEventType($request->event_type);
        }

        // Filter by severity
        if ($request->filled('severity')) {
            $query->bySeverity($request->severity);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'suspicious') {
                $query->suspicious();
            } elseif ($request->status === 'requires_review') {
                $query->requiresReview();
            } elseif ($request->status === 'unresolved') {
                $query->unresolved();
            }
        }

        $securityLogs = $query->orderBy('created_at', 'desc')->paginate(20);
        $users = User::all();
        $eventTypes = SecurityLog::select('event_type')->distinct()->pluck('event_type');
        $severities = SecurityLog::select('severity')->distinct()->pluck('severity');

        return view('security.logs', compact('securityLogs', 'users', 'eventTypes', 'severities'));
    }

    /**
     * Show audit logs
     */
    public function auditLogs(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $query = AuditLog::with('user');

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

        // Filter by compliance
        if ($request->filled('compliance')) {
            if ($request->compliance === 'related') {
                $query->complianceRelated();
            } elseif ($request->compliance === 'standard') {
                $query->byComplianceStandard($request->compliance_standard);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $auditLogs = $query->orderBy('created_at', 'desc')->paginate(20);
        $users = User::all();
        $actions = AuditLog::select('action')->distinct()->pluck('action');
        $resourceTypes = AuditLog::select('resource_type')->distinct()->pluck('resource_type');
        $complianceStandards = AuditLog::select('compliance_standard')->distinct()->whereNotNull('compliance_standard')->pluck('compliance_standard');

        return view('security.audit-logs', compact('auditLogs', 'users', 'actions', 'resourceTypes', 'complianceStandards'));
    }

    /**
     * Show user sessions
     */
    public function userSessions(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $query = UserSession::with('user');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        // Filter by device type
        if ($request->filled('device_type')) {
            $query->byDeviceType($request->device_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->expired();
            } elseif ($request->status === 'secure') {
                $query->secure();
            } elseif ($request->status === 'mfa_verified') {
                $query->mfaVerified();
            }
        }

        $sessions = $query->orderBy('last_activity', 'desc')->paginate(20);
        $users = User::all();

        return view('security.sessions', compact('sessions', 'users'));
    }

    /**
     * Show security policies
     */
    public function policies()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $policies = SecurityPolicy::with(['createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('security.policies', compact('policies'));
    }

    /**
     * Create new security policy
     */
    public function createPolicy(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', [
                SecurityPolicy::TYPE_PASSWORD,
                SecurityPolicy::TYPE_SESSION,
                SecurityPolicy::TYPE_MFA,
                SecurityPolicy::TYPE_ENCRYPTION,
                SecurityPolicy::TYPE_ACCESS_CONTROL,
                SecurityPolicy::TYPE_AUDIT,
                SecurityPolicy::TYPE_DATA_RETENTION,
                SecurityPolicy::TYPE_PRIVACY,
            ]),
            'description' => 'required|string',
            'settings' => 'required|array',
            'is_global' => 'boolean',
            'applies_to' => 'nullable|array',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $policy = SecurityPolicy::create([
                'name' => $request->name,
                'type' => $request->type,
                'description' => $request->description,
                'settings' => $request->settings,
                'is_global' => $request->boolean('is_global', false),
                'applies_to' => $request->applies_to,
                'effective_from' => $request->effective_from,
                'effective_until' => $request->effective_until,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Clear cache
            Cache::forget("password_policy_{$user->id}");
            Cache::forget("session_timeout_{$user->id}");
            Cache::forget("max_sessions_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Security policy created successfully',
                'policy' => $policy,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create policy: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update security policy
     */
    public function updatePolicy(Request $request, SecurityPolicy $policy): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'settings' => 'sometimes|required|array',
            'is_active' => 'sometimes|boolean',
            'is_global' => 'sometimes|boolean',
            'applies_to' => 'sometimes|nullable|array',
            'effective_from' => 'sometimes|nullable|date',
            'effective_until' => 'sometimes|nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $policy->update(array_merge($request->only([
                'name', 'description', 'settings', 'is_active', 'is_global',
                'applies_to', 'effective_from', 'effective_until'
            ]), [
                'updated_by' => $user->id,
            ]));

            // Clear cache
            Cache::forget("password_policy_{$user->id}");
            Cache::forget("session_timeout_{$user->id}");
            Cache::forget("max_sessions_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Security policy updated successfully',
                'policy' => $policy->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update policy: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete security policy
     */
    public function deletePolicy(SecurityPolicy $policy): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $policy->delete();

            // Clear cache
            Cache::forget("password_policy_{$user->id}");
            Cache::forget("session_timeout_{$user->id}");
            Cache::forget("max_sessions_{$user->id}");

            return response()->json([
                'success' => true,
                'message' => 'Security policy deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete policy: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Deactivate user session
     */
    public function deactivateSession(UserSession $session): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin && $user->id !== $session->user_id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $session->deactivate();

            return response()->json([
                'success' => true,
                'message' => 'Session deactivated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to deactivate session: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Deactivate all sessions for a user
     */
    public function deactivateAllUserSessions(User $targetUser): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin && $user->id !== $targetUser->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $deactivatedCount = $this->securityService->deactivateAllUserSessions($targetUser->id);

            return response()->json([
                'success' => true,
                'message' => "Deactivated {$deactivatedCount} sessions successfully",
                'deactivated_count' => $deactivatedCount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to deactivate sessions: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Mark security event as resolved
     */
    public function resolveSecurityEvent(SecurityLog $securityLog, Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'resolution_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $securityLog->markAsResolved($request->resolution_notes);

            return response()->json([
                'success' => true,
                'message' => 'Security event marked as resolved',
                'security_log' => $securityLog->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to resolve event: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get security statistics API
     */
    public function getSecurityStats(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $days = $request->get('days', 30);
        $stats = $this->securityService->getSecurityStatistics($days);

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Export security logs
     */
    public function exportSecurityLogs(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'format' => 'required|in:json,csv',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after:date_from',
            'event_type' => 'nullable|string',
            'severity' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $query = SecurityLog::with('user');

            if ($request->filled('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
            }

            if ($request->filled('event_type')) {
                $query->byEventType($request->event_type);
            }

            if ($request->filled('severity')) {
                $query->bySeverity($request->severity);
            }

            $logs = $query->orderBy('created_at', 'desc')->get();

            $filename = 'security_logs_' . now()->format('Y-m-d_H-i-s') . '.' . $request->format;
            $filePath = storage_path('app/security-exports/' . $filename);

            // Ensure directory exists
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0755, true);
            }

            if ($request->format === 'json') {
                file_put_contents($filePath, json_encode($logs, JSON_PRETTY_PRINT));
            } else {
                $handle = fopen($filePath, 'w');
                fputcsv($handle, ['ID', 'User', 'Event Type', 'Severity', 'Description', 'IP Address', 'Location', 'Created At']);
                
                foreach ($logs as $log) {
                    fputcsv($handle, [
                        $log->id,
                        $log->user ? $log->user->name : 'System',
                        $log->event_type_label,
                        $log->severity,
                        $log->description,
                        $log->ip_address,
                        $log->location_info,
                        $log->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
                
                fclose($handle);
            }

            return response()->json([
                'success' => true,
                'message' => 'Security logs exported successfully',
                'download_url' => route('security.download-export', $filename),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to export logs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download exported file
     */
    public function downloadExport($filename)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $filePath = storage_path('app/security-exports/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath);
    }
} 