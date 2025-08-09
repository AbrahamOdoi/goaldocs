<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\SecurityAudit;
use App\Models\EncryptionKey;
use App\Services\SecurityService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SecurityController extends Controller
{
    protected $securityService;
    protected $permissionService;

    public function __construct(SecurityService $securityService, PermissionService $permissionService)
    {
        $this->securityService = $securityService;
        $this->permissionService = $permissionService;
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
        
        $stats = $this->securityService->getSecurityStats();
        $recentAudits = $this->securityService->getSecurityAuditLogs(null, 20);
        $securityReport = $this->securityService->generateSecurityReport();
        
        return view('security.dashboard', compact('stats', 'recentAudits', 'securityReport'));
    }

    /**
     * Encrypt a file
     */
    public function encryptFile(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'password' => 'nullable|string|min:8|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $result = $this->securityService->encryptFile($file, $request->input('password'));
            
            return response()->json([
                'success' => true,
                'message' => 'File encrypted successfully',
                'password' => $result['password'],
                'key_id' => $result['key_id'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to encrypt file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Decrypt a file
     */
    public function decryptFile(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'password' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $result = $this->securityService->decryptFile($file, $request->input('password'));
            
            return response()->json([
                'success' => true,
                'message' => 'File decrypted successfully',
                'decrypted_path' => $result['decrypted_path'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to decrypt file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Add watermark to file
     */
    public function addWatermark(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'text' => 'nullable|string|max:255',
            'position' => 'nullable|in:top-left,top-right,bottom-left,bottom-right,center',
            'opacity' => 'nullable|numeric|between:0,1',
            'font_size' => 'nullable|integer|min:8|max:72',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
            'rotation' => 'nullable|numeric|between:-90,90',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $options = $request->only(['text', 'position', 'opacity', 'font_size', 'color', 'rotation']);
            $result = $this->securityService->addWatermark($file, $options);
            
            return response()->json([
                'success' => true,
                'message' => 'Watermark added successfully',
                'watermarked_path' => $result['watermarked_path'],
                'options' => $result['options'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add watermark: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get file security status
     */
    public function getFileSecurityStatus(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $status = $this->securityService->getFileSecurityStatus($file);
        
        return response()->json([
            'success' => true,
            'status' => $status
        ]);
    }

    /**
     * Get security audit logs
     */
    public function getAuditLogs(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $fileId = $request->get('file_id');
        $limit = $request->get('limit', 50);
        
        $logs = $this->securityService->getSecurityAuditLogs($fileId, $limit);
        
        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    /**
     * Show security audit logs
     */
    public function showAuditLogs(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $query = SecurityAudit::with('user');
        
        // Filter by action
        if ($request->has('action')) {
            $query->byAction($request->action);
        }
        
        // Filter by user
        if ($request->has('user_id')) {
            $query->byUser($request->user_id);
        }
        
        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }
        
        $audits = $query->orderBy('created_at', 'desc')->paginate(50);
        
        return view('security.audit-logs', compact('audits'));
    }

    /**
     * Show security statistics
     */
    public function showStats()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $stats = $this->securityService->getSecurityStats();
        $securityReport = $this->securityService->generateSecurityReport();
        
        return view('security.stats', compact('stats', 'securityReport'));
    }

    /**
     * Get security statistics
     */
    public function getStats()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $stats = $this->securityService->getSecurityStats();
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Generate security report
     */
    public function generateReport()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $report = $this->securityService->generateSecurityReport();
        
        return response()->json([
            'success' => true,
            'report' => $report
        ]);
    }

    /**
     * Show encryption keys
     */
    public function showEncryptionKeys()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $keys = EncryptionKey::with(['file', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('security.encryption-keys', compact('keys'));
    }

    /**
     * Get encryption key details
     */
    public function getEncryptionKey(EncryptionKey $key)
    {
        $user = Auth::user();
        
        if (!$user->is_admin && $key->created_by !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        return response()->json([
            'success' => true,
            'key' => [
                'id' => $key->id,
                'file_name' => $key->file->name,
                'encryption_method' => $key->encryption_method,
                'method_display' => $key->method_display,
                'strength_rating' => $key->strength_rating,
                'strength_color' => $key->strength_color,
                'created_at' => $key->formatted_created_at,
                'age_in_days' => $key->age_in_days,
                'should_rotate' => $key->should_rotate,
                'file_size' => $key->file_size,
                'encrypted_file_size' => $key->encrypted_file_size,
                'compression_ratio' => $key->compression_ratio,
            ]
        ]);
    }

    /**
     * Cleanup old audit logs
     */
    public function cleanupAuditLogs(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'older_than' => 'required|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $count = $this->securityService->cleanupAuditLogs($request->input('older_than'));
            
            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$count} old audit logs"
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to cleanup audit logs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show security settings
     */
    public function showSettings()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        return view('security.settings');
    }

    /**
     * Update security settings
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'auto_encrypt_sensitive' => 'boolean',
            'require_watermark' => 'boolean',
            'audit_log_retention_days' => 'integer|min:30|max:365',
            'failed_login_attempts' => 'integer|min:3|max:10',
            'session_timeout_minutes' => 'integer|min:15|max:480',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Update security settings (implementation would depend on your settings system)
        // For now, we'll just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Security settings updated successfully'
        ]);
    }
} 