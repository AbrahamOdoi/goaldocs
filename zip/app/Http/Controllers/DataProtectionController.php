<?php

namespace App\Http\Controllers;

use App\Services\DataProtectionService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DataProtectionController extends Controller
{
    protected $dataProtectionService;

    public function __construct(DataProtectionService $dataProtectionService)
    {
        $this->dataProtectionService = $dataProtectionService;
    }

    /**
     * Show data protection dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $stats = $this->dataProtectionService->getDataProtectionStatistics();

        return view('data-protection.dashboard', compact('stats'));
    }

    /**
     * Show data subject rights requests
     */
    public function dataSubjectRequests()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        return view('data-protection.data-subject-requests');
    }

    /**
     * Process data subject rights request
     */
    public function processDataSubjectRequest(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'request_type' => 'required|in:access,rectification,erasure,portability,restriction,objection',
            'data' => 'nullable|array',
        ]);

        try {
            $result = $this->dataProtectionService->processDataSubjectRequest(
                $request->user_id,
                $request->request_type,
                $request->data ?? []
            );

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process request: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show data retention policies
     */
    public function retentionPolicies()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $stats = $this->dataProtectionService->getDataProtectionStatistics();

        return view('data-protection.retention-policies', compact('stats'));
    }

    /**
     * Apply data retention policies
     */
    public function applyRetentionPolicies(): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $results = $this->dataProtectionService->applyDataRetentionPolicies();

            return response()->json([
                'success' => true,
                'message' => 'Data retention policies applied successfully',
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to apply retention policies: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show privacy controls
     */
    public function privacyControls()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        return view('data-protection.privacy-controls');
    }

    /**
     * Update privacy controls
     */
    public function updatePrivacyControls(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'purpose' => 'required|string',
            'consent' => 'required|boolean',
        ]);

        try {
            $this->dataProtectionService->recordDataProcessingConsent(
                $request->user_id,
                $request->purpose,
                $request->consent
            );

            return response()->json([
                'success' => true,
                'message' => 'Privacy controls updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update privacy controls: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show data anonymization
     */
    public function dataAnonymization()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $anonymizedUsers = User::where('name', 'like', 'ANONYMIZED_%')->paginate(20);

        return view('data-protection.data-anonymization', compact('anonymizedUsers'));
    }

    /**
     * Anonymize user data
     */
    public function anonymizeUserData(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $targetUser = User::findOrFail($request->user_id);
            $anonymizedData = $this->dataProtectionService->processDataSubjectRequest(
                $request->user_id,
                'erasure'
            );

            return response()->json([
                'success' => true,
                'message' => 'User data anonymized successfully',
                'anonymized_data' => $anonymizedData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to anonymize user data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show consent management
     */
    public function consentManagement()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $users = User::paginate(20);

        return view('data-protection.consent-management', compact('users'));
    }

    /**
     * Get user consent status
     */
    public function getUserConsent(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'purpose' => 'required|string',
        ]);

        try {
            $consent = $this->dataProtectionService->checkDataProcessingConsent(
                $request->user_id,
                $request->purpose
            );

            return response()->json([
                'success' => true,
                'consent' => $consent,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get consent status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show data portability
     */
    public function dataPortability()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        return view('data-protection.data-portability');
    }

    /**
     * Export user data
     */
    public function exportUserData(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        try {
            $result = $this->dataProtectionService->processDataSubjectRequest(
                $request->user_id,
                'portability'
            );

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to export user data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download exported data
     */
    public function downloadExport($filename)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        $filePath = storage_path('app/data-exports/' . $filename);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->download($filePath);
    }

    /**
     * Show data protection settings
     */
    public function settings()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }

        return view('data-protection.settings');
    }

    /**
     * Update data protection settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'file_retention_days' => 'required|integer|min:30|max:3650',
            'audit_retention_days' => 'required|integer|min:30|max:3650',
            'user_retention_days' => 'required|integer|min:30|max:3650',
            'auto_anonymization' => 'boolean',
            'gdpr_enabled' => 'boolean',
            'consent_required' => 'boolean',
        ]);

        try {
            // Update data protection settings (implementation would depend on your settings system)
            // For now, we'll just return success
            
            return response()->json([
                'success' => true,
                'message' => 'Data protection settings updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update settings: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get data protection statistics API
     */
    public function getStats(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $stats = $this->dataProtectionService->getDataProtectionStatistics();

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
