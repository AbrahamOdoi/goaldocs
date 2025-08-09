<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\FileVersion;
use App\Services\VersionControlService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VersionControlController extends Controller
{
    protected $versionService;
    protected $permissionService;

    public function __construct(VersionControlService $versionService, PermissionService $permissionService)
    {
        $this->versionService = $versionService;
        $this->permissionService = $permissionService;
    }

    /**
     * Show version history for a file
     */
    public function showHistory(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            abort(403, 'Access denied');
        }
        
        $versionHistory = $this->versionService->getVersionHistory($file);
        $versionStats = $this->versionService->getVersionStats($file);
        
        return view('files.version-history', compact('file', 'versionHistory', 'versionStats'));
    }

    /**
     * Upload a new version
     */
    public function uploadVersion(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:' . (100 * 1024 * 1024), // 100MB
            'change_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $version = $this->versionService->createVersion(
                $file, 
                $request->file('file'), 
                $request->input('change_notes')
            );
            
            return response()->json([
                'success' => true,
                'message' => 'New version uploaded successfully',
                'version' => [
                    'id' => $version->id,
                    'version_number' => $version->version_number,
                    'file_size' => $version->file_size,
                    'uploaded_at' => $version->created_at->format('M j, Y g:i A'),
                    'change_notes' => $version->change_notes,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to upload new version: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Compare two versions
     */
    public function compareVersions(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'version1_id' => 'required|exists:file_versions,id',
            'version2_id' => 'required|exists:file_versions,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $version1 = FileVersion::find($request->version1_id);
        $version2 = FileVersion::find($request->version2_id);
        
        // Ensure both versions belong to the same file
        if ($version1->file_id !== $file->id || $version2->file_id !== $file->id) {
            return response()->json(['error' => 'Invalid version selection'], 400);
        }
        
        try {
            $comparison = $this->versionService->compareVersions($version1, $version2);
            
            return response()->json([
                'success' => true,
                'comparison' => $comparison
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to compare versions: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Rollback to a specific version
     */
    public function rollbackVersion(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'version_id' => 'required|exists:file_versions,id',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $targetVersion = FileVersion::find($request->version_id);
        
        // Ensure version belongs to the file
        if ($targetVersion->file_id !== $file->id) {
            return response()->json(['error' => 'Invalid version selection'], 400);
        }
        
        // Don't allow rollback to current version
        if ($targetVersion->is_current) {
            return response()->json(['error' => 'Cannot rollback to current version'], 400);
        }
        
        try {
            $success = $this->versionService->rollbackToVersion($file, $targetVersion);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully rolled back to version {$targetVersion->version_number}",
                    'new_current_version' => $targetVersion->version_number,
                ]);
            } else {
                return response()->json(['error' => 'Failed to rollback version'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to rollback version: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a version
     */
    public function deleteVersion(Request $request, FileVersion $version)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $version->file, 'delete')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        // Don't allow deletion of current version
        if ($version->is_current) {
            return response()->json(['error' => 'Cannot delete current version'], 400);
        }
        
        try {
            $success = $this->versionService->deleteVersion($version);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => "Version {$version->version_number} deleted successfully"
                ]);
            } else {
                return response()->json(['error' => 'Failed to delete version'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete version: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download a specific version
     */
    public function downloadVersion(FileVersion $version)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $version->file, 'download')) {
            abort(403, 'Access denied');
        }
        
        if (!Storage::disk('local')->exists($version->file_path)) {
            abort(404, 'Version file not found');
        }
        
        $path = Storage::disk('local')->path($version->file_path);
        $filename = $version->file->name . '_v' . $version->version_number . '.' . pathinfo($path, PATHINFO_EXTENSION);
        
        return response()->download($path, $filename);
    }

    /**
     * Get version preview
     */
    public function previewVersion(FileVersion $version)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $version->file, 'view')) {
            abort(403, 'Access denied');
        }
        
        if (!Storage::disk('local')->exists($version->file_path)) {
            abort(404, 'Version file not found');
        }
        
        // For now, redirect to download - in future, implement version-specific preview
        return redirect()->route('files.version.download', $version);
    }

    /**
     * Get version metadata
     */
    public function getVersionMetadata(FileVersion $version)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $version->file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $metadata = $this->versionService->getVersionInfo($version);
        
        return response()->json([
            'success' => true,
            'metadata' => $metadata
        ]);
    }

    /**
     * Get version statistics
     */
    public function getVersionStats(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $stats = $this->versionService->getVersionStats($file);
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Show version comparison interface
     */
    public function showComparison(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            abort(403, 'Access denied');
        }
        
        $version1Id = $request->get('version1');
        $version2Id = $request->get('version2');
        
        $version1 = null;
        $version2 = null;
        $comparison = null;
        
        if ($version1Id && $version2Id) {
            $version1 = FileVersion::where('id', $version1Id)->where('file_id', $file->id)->first();
            $version2 = FileVersion::where('id', $version2Id)->where('file_id', $file->id)->first();
            
            if ($version1 && $version2) {
                $comparison = $this->versionService->compareVersions($version1, $version2);
            }
        }
        
        $versionHistory = $this->versionService->getVersionHistory($file);
        
        return view('files.version-comparison', compact('file', 'version1', 'version2', 'comparison', 'versionHistory'));
    }

    /**
     * AJAX endpoint for version operations
     */
    public function ajaxVersionOperation(Request $request, File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $operation = $request->get('operation');
        
        switch ($operation) {
            case 'history':
                $versionHistory = $this->versionService->getVersionHistory($file);
                return response()->json(['success' => true, 'history' => $versionHistory]);
                
            case 'stats':
                $stats = $this->versionService->getVersionStats($file);
                return response()->json(['success' => true, 'stats' => $stats]);
                
            case 'compare':
                return $this->compareVersions($request, $file);
                
            default:
                return response()->json(['error' => 'Invalid operation'], 400);
        }
    }
} 