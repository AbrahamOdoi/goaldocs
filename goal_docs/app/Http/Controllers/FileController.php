<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\FileVersion;
use App\Models\FilePermission;
use App\Models\RecentActivity;
use App\Models\User;
use App\Models\Position;
use App\Models\Department;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class FileController extends Controller
{
    /**
     * File type blacklist for security
     */
    private $blacklistedExtensions = [
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 
        'msi', 'app', 'deb', 'rpm', 'dmg', 'pkg', 'sh', 'bin', 'run'
    ];

    /**
     * Maximum file size in bytes (100MB)
     */
    private $maxFileSize = 100 * 1024 * 1024;

    /**
     * Permission service
     */
    private $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    private function checkUserAccess()
    {
        // All authenticated users can access files
        // Individual users get personal file space
        // Other user types get shared organizational file space
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }
    }

    /**
     * Display file browser interface
     */
    public function index(Request $request)
    {
        $this->checkUserAccess();
        
        $user = Auth::user();
        $folderId = $request->get('folder');
        
        // Get current folder
        $currentFolder = null;
        if ($folderId) {
            $currentFolder = Folder::forUserType($user->type)
                ->where('id', $folderId)
                ->first();
                
            if (!$currentFolder) {
                return redirect()->route('files.index')->with('error', 'Folder not found');
            }
        }
        
        // Get folders and files in current directory for this specific organization
        if ($user->type === 'individual') {
            $foldersQuery = Folder::forUserType($user->type)->active();
            $filesQuery = File::forUserType($user->type)->active();
        } else {
            // Use organization-specific filtering for non-individual users
            $foldersQuery = Folder::forOrganization($user->type, $user->type_name)->active();
            $filesQuery = File::forOrganization($user->type, $user->type_name)->active();
        }
        
        if ($currentFolder) {
            $foldersQuery->where('parent_folder_id', $currentFolder->id);
            $filesQuery->where('folder_id', $currentFolder->id);
        } else {
            $foldersQuery->whereNull('parent_folder_id');
            $filesQuery->whereNull('folder_id');
        }
        
        $folders = $foldersQuery->orderBy('name')->get();
        $files = $filesQuery->orderBy('name')->get();
        
        // Build breadcrumb trail
        $breadcrumbs = $this->buildBreadcrumbs($currentFolder);
        
        return view('files.index', compact('folders', 'files', 'currentFolder', 'breadcrumbs'));
    }

    /**
     * Create a new folder
     */
    public function createFolder(Request $request): JsonResponse
    {
        $this->checkUserAccess();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_folder_id' => 'nullable|exists:folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();

        // Check if user has permission to create folders in the parent folder
        if ($request->parent_folder_id) {
            $parentFolder = Folder::find($request->parent_folder_id);
            if ($parentFolder && !$this->permissionService->userHasPermission($user, $parentFolder, 'upload')) {
                return response()->json(['error' => 'You do not have permission to create folders in this location'], 403);
            }
        }

        // Check if folder with same name exists in the same parent within the same organization
        if ($user->type === 'individual') {
            $exists = Folder::forUserType($user->type)
                ->where('parent_folder_id', $request->parent_folder_id)
                ->where('name', $request->name)
                ->exists();
        } else {
            $exists = Folder::forOrganization($user->type, $user->type_name)
                ->where('parent_folder_id', $request->parent_folder_id)
                ->where('name', $request->name)
                ->exists();
        }

        if ($exists) {
            return response()->json(['error' => 'A folder with this name already exists'], 400);
        }

        $folder = Folder::create([
            'name' => $request->name,
            'description' => $request->description,
            'parent_folder_id' => $request->parent_folder_id,
            'user_type' => $user->type,
            'type_name' => $user->type_name,
            'created_by' => $user->id,
        ]);

        // Automatically grant full permissions to the creator
        $fullPermissions = [
            'view' => true,
            'download' => true,
            'edit' => true,
            'upload' => true,
            'delete' => true,
            'reshare' => true,
            'manage' => true
        ];
        
        $this->permissionService->assignPermission(
            $folder,
            $user,
            $fullPermissions,
            $user,
            'Automatic permissions for folder creator'
        );

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'upload',
            null,
            $folder->id,
            ['folder_name' => $folder->name]
        );

        return response()->json([
            'success' => true,
            'folder' => $folder,
            'message' => 'Folder created successfully'
        ]);
    }

    /**
     * Upload files
     */
    public function upload(Request $request): JsonResponse
    {
        $this->checkUserAccess();
        
        $validator = Validator::make($request->all(), [
            'files' => 'required|array',
            'files.*' => 'file|max:' . ($this->maxFileSize / 1024), // Laravel expects KB
            'folder_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        $uploadedFiles = [];
        $errors = [];

        // Check if user has permission to upload to the target folder
        if ($request->folder_id) {
            $targetFolder = Folder::find($request->folder_id);
            if ($targetFolder && !$this->permissionService->userHasPermission($user, $targetFolder, 'upload')) {
                return response()->json(['error' => 'You do not have permission to upload files to this folder'], 403);
            }
        }

        foreach ($request->file('files') as $uploadedFile) {
            try {
                // Validate file type
                $extension = strtolower($uploadedFile->getClientOriginalExtension());
                if (in_array($extension, $this->blacklistedExtensions)) {
                    $errors[] = "File '{$uploadedFile->getClientOriginalName()}' has a forbidden file type";
                    continue;
                }

                // Generate unique filename
                $filename = Str::uuid() . '.' . $extension;
                $path = 'files/' . $user->type . '/' . date('Y/m');
                
                // Store file
                $filePath = $uploadedFile->storeAs($path, $filename, 'local');
                
                // Calculate file hash for duplicate detection
                $fileHash = hash_file('sha256', $uploadedFile->getPathname());
                
                // Check for duplicates within the same organization
                if ($user->type === 'individual') {
                    $existingFile = File::forUserType($user->type)
                        ->where('file_hash', $fileHash)
                        ->first();
                } else {
                    $existingFile = File::forOrganization($user->type, $user->type_name)
                        ->where('file_hash', $fileHash)
                        ->first();
                }
                    
                if ($existingFile) {
                    Storage::disk('local')->delete($filePath); // Clean up uploaded file
                    $errors[] = "File '{$uploadedFile->getClientOriginalName()}' already exists as '{$existingFile->name}'";
                    continue;
                }

                // Create file record
                $file = File::create([
                    'name' => pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME),
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'description' => $request->description,
                    'file_path' => $filePath,
                    'file_hash' => $fileHash,
                    'file_size' => $uploadedFile->getSize(),
                    'mime_type' => $uploadedFile->getMimeType(),
                    'extension' => $extension,
                    'folder_id' => $request->folder_id,
                    'user_type' => $user->type,
                    'type_name' => $user->type_name,
                    'uploaded_by' => $user->id,
                ]);

                // Create initial version
                FileVersion::create([
                    'file_id' => $file->id,
                    'version_number' => 1,
                    'file_path' => $filePath,
                    'file_size' => $uploadedFile->getSize(),
                    'mime_type' => $uploadedFile->getMimeType(),
                    'uploaded_by' => $user->id,
                    'change_notes' => 'Initial upload',
                    'is_current' => true,
                ]);

                // Automatically grant full permissions to the uploader
                $fullPermissions = [
                    'view' => true,
                    'download' => true,
                    'edit' => true,
                    'upload' => true,
                    'delete' => true,
                    'reshare' => true,
                    'manage' => true
                ];
                
                $this->permissionService->assignPermission(
                    $file,
                    $user,
                    $fullPermissions,
                    $user,
                    'Automatic permissions for file uploader'
                );

                // Log activity
                RecentActivity::log(
                    $user->id,
                    $user->type,
                    $user->type_name,
                    'upload',
                    $file->id,
                    null,
                    ['file_name' => $file->original_name, 'file_size' => $file->file_size]
                );

                $uploadedFiles[] = $file;

            } catch (\Exception $e) {
                $errors[] = "Failed to upload '{$uploadedFile->getClientOriginalName()}': " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => count($uploadedFiles) > 0,
            'uploaded_files' => $uploadedFiles,
            'errors' => $errors,
            'message' => count($uploadedFiles) . ' file(s) uploaded successfully'
        ]);
    }

    /**
     * Download a file
     */
    public function download(File $file)
    {
        $this->checkUserAccess();
        
        $user = Auth::user();
        
        // Check if user has permission to download this file
        if (!$this->permissionService->userHasPermission($user, $file, 'download')) {
            abort(403, 'Access denied');
        }
        
        // Update last accessed timestamp
        $file->updateLastAccessed();
        
        if (!Storage::disk('local')->exists($file->file_path)) {
            abort(404, 'File not found');
        }

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'download',
            $file->id,
            null,
            ['file_name' => $file->original_name]
        );

        return response()->download(Storage::disk('local')->path($file->file_path), $file->original_name);
    }

    /**
     * Update file details
     */
    public function update(Request $request, File $file): JsonResponse
    {
        $this->checkUserAccess();
        
        $user = Auth::user();
        
        // Check if user has permission to edit this file
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            abort(403, 'Access denied');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Check if file with same name exists in the same folder within the same organization
        if ($user->type === 'individual') {
            $exists = File::forUserType($user->type)
                ->where('folder_id', $file->folder_id)
                ->where('name', $request->name)
                ->where('id', '!=', $file->id)
                ->exists();
        } else {
            $exists = File::forOrganization($user->type, $user->type_name)
                ->where('folder_id', $file->folder_id)
                ->where('name', $request->name)
                ->where('id', '!=', $file->id)
                ->exists();
        }

        if ($exists) {
            return response()->json(['error' => 'A file with this name already exists in this folder'], 400);
        }

        $file->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'edit',
            $file->id,
            null,
            ['file_name' => $file->original_name, 'changes' => ['name' => $request->name, 'description' => $request->description]]
        );

        return response()->json([
            'success' => true,
            'file' => $file,
            'message' => 'File updated successfully'
        ]);
    }

    /**
     * Update folder details
     */
    public function updateFolder(Request $request, Folder $folder): JsonResponse
    {
        $this->checkUserAccess();
        
        $user = Auth::user();
        
        // Check if user has permission to edit this folder
        if (!$this->permissionService->userHasPermission($user, $folder, 'edit')) {
            abort(403, 'Access denied');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Check if folder with same name exists in the same parent within the same organization
        if ($user->type === 'individual') {
            $exists = Folder::forUserType($user->type)
                ->where('parent_folder_id', $folder->parent_folder_id)
                ->where('name', $request->name)
                ->where('id', '!=', $folder->id)
                ->exists();
        } else {
            $exists = Folder::forOrganization($user->type, $user->type_name)
                ->where('parent_folder_id', $folder->parent_folder_id)
                ->where('name', $request->name)
                ->where('id', '!=', $folder->id)
                ->exists();
        }

        if ($exists) {
            return response()->json(['error' => 'A folder with this name already exists in this location'], 400);
        }

        $folder->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'edit',
            null,
            $folder->id,
            ['folder_name' => $folder->name, 'changes' => ['name' => $request->name, 'description' => $request->description]]
        );

        return response()->json([
            'success' => true,
            'folder' => $folder,
            'message' => 'Folder updated successfully'
        ]);
    }

    /**
     * Preview a file (for images, PDFs, etc.)
     */
    public function preview(File $file): Response
    {
        $this->checkUserAccess();
        
        $user = Auth::user();
        
        // Check if user has permission to view this file
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            abort(403, 'Access denied');
        }
        
        // Update last accessed timestamp
        $file->updateLastAccessed();
        
        if (!Storage::disk('local')->exists($file->file_path)) {
            abort(404, 'File not found');
        }

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'view',
            $file->id,
            null,
            ['file_name' => $file->original_name]
        );

        // For images, serve them directly with proper headers
        if ($file->is_image) {
            $fileContent = Storage::disk('local')->get($file->file_path);
            
            return response($fileContent)
                ->header('Content-Type', $file->mime_type)
                ->header('Content-Disposition', 'inline; filename="' . $file->original_name . '"')
                ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
        }
        
        // For other file types, serve as download
        $fileContent = Storage::disk('local')->get($file->file_path);
        
        return response($fileContent)
            ->header('Content-Type', $file->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $file->original_name . '"');
    }

    /**
     * Delete a file
     */
    public function destroy(File $file): JsonResponse
    {
        $this->checkUserAccess();
        
        $user = Auth::user();
        
        // Check if user has permission to delete this file
        if (!$this->permissionService->userHasPermission($user, $file, 'delete')) {
            abort(403, 'Access denied');
        }
        
        try {
            // Log activity before deletion
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'delete',
                $file->id,
                null,
                ['file_name' => $file->original_name]
            );
            
            $file->deleteFile();
            return response()->json(['success' => true, 'message' => 'File deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete file'], 500);
        }
    }

    /**
     * Delete a folder
     */
    public function deleteFolder(Folder $folder): JsonResponse
    {
        $this->checkUserAccess();
        
        $user = Auth::user();
        
        // Check if user has permission to delete this folder
        if (!$this->permissionService->userHasPermission($user, $folder, 'delete')) {
            abort(403, 'Access denied');
        }
        
        try {
            // Check if folder has contents
            $hasFiles = $folder->files()->exists();
            $hasSubfolders = $folder->children()->exists();
            
            if ($hasFiles || $hasSubfolders) {
                return response()->json(['error' => 'Cannot delete folder that contains files or subfolders'], 400);
            }
            
            // Log activity before deletion
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'delete',
                null,
                $folder->id,
                ['folder_name' => $folder->name]
            );
            
            $folder->delete();
            return response()->json(['success' => true, 'message' => 'Folder deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete folder'], 500);
        }
    }

    /**
     * Rename a file
     */
    public function rename(Request $request, File $file): JsonResponse
    {
        $this->checkUserAccess();
        
        $user = Auth::user();
        
        // Check if user has permission to edit this file
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            abort(403, 'Access denied');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $file->update(['name' => $request->name]);

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'edit',
            $file->id,
            null,
            ['file_name' => $file->original_name, 'action' => 'rename', 'new_name' => $request->name]
        );

        return response()->json(['success' => true, 'message' => 'File renamed successfully']);
    }

    /**
     * Rename a folder
     */
    public function renameFolder(Request $request, Folder $folder): JsonResponse
    {
        $this->checkUserAccess();
        
        $user = Auth::user();
        
        // Check if user has permission to edit this folder
        if (!$this->permissionService->userHasPermission($user, $folder, 'edit')) {
            abort(403, 'Access denied');
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // Check if folder with same name exists in the same parent
        $exists = Folder::forUserType($folder->user_type)
            ->where('parent_folder_id', $folder->parent_folder_id)
            ->where('name', $request->name)
            ->where('id', '!=', $folder->id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'A folder with this name already exists'], 400);
        }

        $folder->update(['name' => $request->name]);

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'edit',
            null,
            $folder->id,
            ['folder_name' => $folder->name, 'action' => 'rename', 'new_name' => $request->name]
        );

        return response()->json(['success' => true, 'message' => 'Folder renamed successfully']);
    }

    /**
     * Move file to another folder
     */
    public function move(Request $request, File $file): JsonResponse
    {
        $this->checkUserAccess();
        
        $user = Auth::user();
        
        // Check if user has permission to edit this file
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            abort(403, 'Access denied');
        }
        
        $validator = Validator::make($request->all(), [
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        // If moving to a specific folder, check if user has upload permission to that folder
        if ($request->folder_id) {
            $targetFolder = Folder::find($request->folder_id);
            if ($targetFolder && !$this->permissionService->userHasPermission($user, $targetFolder, 'upload')) {
                return response()->json(['error' => 'You do not have permission to upload files to the target folder'], 403);
            }
        }

        $oldFolderId = $file->folder_id;
        $file->update(['folder_id' => $request->folder_id]);

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'edit',
            $file->id,
            null,
            ['file_name' => $file->original_name, 'action' => 'move', 'from_folder' => $oldFolderId, 'to_folder' => $request->folder_id]
        );

        return response()->json(['success' => true, 'message' => 'File moved successfully']);
    }

    /**
     * Search files and folders
     */
    public function search(Request $request): JsonResponse
    {
        $this->checkUserAccess();
        
        $query = $request->get('q');
        if (!$query) {
            return response()->json(['files' => [], 'folders' => []]);
        }

        $user = Auth::user();

        $files = File::forUserType($user->type)
            ->active()
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('original_name', 'like', "%{$query}%");
            })
            ->with(['folder', 'uploader'])
            ->limit(20)
            ->get();

        $folders = Folder::forUserType($user->type)
            ->active()
            ->where('name', 'like', "%{$query}%")
            ->with('creator')
            ->limit(20)
            ->get();

        return response()->json([
            'files' => $files,
            'folders' => $folders
        ]);
    }

    /**
     * Build breadcrumb trail for navigation
     */
    private function buildBreadcrumbs(?Folder $folder): array
    {
        $breadcrumbs = [
            ['name' => 'Files', 'url' => route('files.index')]
        ];

        if (!$folder) {
            return $breadcrumbs;
        }

        $path = [];
        $current = $folder;

        while ($current) {
            array_unshift($path, $current);
            $current = $current->parent;
        }

        foreach ($path as $folder) {
            $breadcrumbs[] = [
                'name' => $folder->name,
                'url' => route('files.index', ['folder' => $folder->id])
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Get permissions for a file or folder
     */
    public function getPermissions(Request $request): JsonResponse
    {
        $this->checkUserAccess();
        
        $validator = Validator::make($request->all(), [
            'resource_type' => 'required|in:file,folder',
            'resource_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        $resource = null;

        if ($request->resource_type === 'file') {
            $resource = File::find($request->resource_id);
        } else {
            $resource = Folder::find($request->resource_id);
        }

        if (!$resource) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        // Check if user can manage permissions
        if (!$this->permissionService->canManagePermissions($user, $resource)) {
            return response()->json(['error' => 'Access denied. You cannot manage permissions for this resource.'], 403);
        }

        $permissions = $this->permissionService->getResourcePermissions($resource);
        $assignableEntities = $this->permissionService->getAssignableEntities($user);

        // Debug logging
        Log::info('Assignable entities for user:', [
            'user_id' => $user->id,
            'user_type' => $user->type,
            'user_type_name' => $user->type_name,
            'entities_count' => count($assignableEntities),
            'users_count' => $assignableEntities['users']->count() ?? 0
        ]);

        return response()->json([
            'permissions' => $permissions,
            'assignable_entities' => $assignableEntities,
            'permission_presets' => FilePermission::getPermissionPresets(),
        ]);
    }

    /**
     * Assign permissions to a file or folder
     */
    public function assignPermissions(Request $request): JsonResponse
    {
        $this->checkUserAccess();
        
        // Debug logging
        Log::info('Permission assignment request:', [
            'resource_type' => $request->resource_type,
            'resource_id' => $request->resource_id,
            'assignable_type' => $request->assignable_type,
            'assignable_id' => $request->assignable_id,
            'permissions' => $request->permissions,
            'user_id' => Auth::id()
        ]);
        
        $validator = Validator::make($request->all(), [
            'resource_type' => 'required|in:file,folder',
            'resource_id' => 'required|integer',
            'assignable_type' => 'required|in:user,position,department,App\\Models\\User,App\\Models\\Position,App\\Models\\Department',
            'assignable_id' => 'required|integer',
            'permissions' => 'required|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            Log::error('Permission assignment validation failed:', $validator->errors()->toArray());
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        $resource = null;

        if ($request->resource_type === 'file') {
            $resource = File::find($request->resource_id);
        } else {
            $resource = Folder::find($request->resource_id);
        }

        if (!$resource) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        // Check if user can manage permissions
        if (!$this->permissionService->canManagePermissions($user, $resource)) {
            return response()->json(['error' => 'Access denied. You cannot manage permissions for this resource.'], 403);
        }

        // Get assignable entity
        $assignable = null;
        switch ($request->assignable_type) {
            case 'user':
            case 'App\\Models\\User':
                $assignable = User::find($request->assignable_id);
                break;
            case 'position':
            case 'App\\Models\\Position':
                $assignable = Position::find($request->assignable_id);
                break;
            case 'department':
            case 'App\\Models\\Department':
                $assignable = Department::find($request->assignable_id);
                break;
        }

        if (!$assignable) {
            return response()->json(['error' => 'Assignable entity not found'], 404);
        }

        // Assign permissions
        $permission = $this->permissionService->assignPermission(
            $resource,
            $assignable,
            $request->permissions,
            $user,
            $request->notes
        );

        // If this is a folder, inherit permissions to children
        if ($resource instanceof Folder) {
            $this->permissionService->inheritPermissionsToChildren($resource);
        }

        return response()->json([
            'success' => true,
            'permission' => $permission,
            'message' => 'Permissions assigned successfully'
        ]);
    }

    /**
     * Remove permissions from a file or folder
     */
    public function removePermissions(Request $request): JsonResponse
    {
        $this->checkUserAccess();
        
        $validator = Validator::make($request->all(), [
            'resource_type' => 'required|in:file,folder',
            'resource_id' => 'required|integer',
            'assignable_type' => 'required|in:user,position,department,App\\Models\\User,App\\Models\\Position,App\\Models\\Department',
            'assignable_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        $resource = null;

        if ($request->resource_type === 'file') {
            $resource = File::find($request->resource_id);
        } else {
            $resource = Folder::find($request->resource_id);
        }

        if (!$resource) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        // Check if user can manage permissions
        if (!$this->permissionService->canManagePermissions($user, $resource)) {
            return response()->json(['error' => 'Access denied. You cannot manage permissions for this resource.'], 403);
        }

        // Get assignable entity
        $assignable = null;
        switch ($request->assignable_type) {
            case 'user':
            case 'App\\Models\\User':
                $assignable = User::find($request->assignable_id);
                break;
            case 'position':
            case 'App\\Models\\Position':
                $assignable = Position::find($request->assignable_id);
                break;
            case 'department':
            case 'App\\Models\\Department':
                $assignable = Department::find($request->assignable_id);
                break;
        }

        if (!$assignable) {
            return response()->json(['error' => 'Assignable entity not found'], 404);
        }

        // Remove permissions
        $removed = $this->permissionService->removePermission($resource, $assignable);

        // If this is a folder, remove inherited permissions
        if ($resource instanceof Folder) {
            $this->permissionService->removeInheritedPermissions(
                $resource,
                get_class($assignable),
                $assignable->id
            );
        }

        return response()->json([
            'success' => $removed,
            'message' => $removed ? 'Permissions removed successfully' : 'No permissions found to remove'
        ]);
    }

    /**
     * Get effective permissions for current user on a resource
     */
    public function getMyPermissions(Request $request): JsonResponse
    {
        $this->checkUserAccess();
        
        $validator = Validator::make($request->all(), [
            'resource_type' => 'required|in:file,folder',
            'resource_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        $resource = null;

        if ($request->resource_type === 'file') {
            $resource = File::find($request->resource_id);
        } else {
            $resource = Folder::find($request->resource_id);
        }

        if (!$resource) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        $effectivePermissions = $this->permissionService->getEffectivePermissions($user, $resource);

        return response()->json([
            'permissions' => $effectivePermissions,
            'can_manage' => $this->permissionService->canManagePermissions($user, $resource),
        ]);
    }

    /**
     * Apply permission preset to a resource
     */
    public function applyPreset(Request $request): JsonResponse
    {
        $this->checkUserAccess();
        
        $validator = Validator::make($request->all(), [
            'resource_type' => 'required|in:file,folder',
            'resource_id' => 'required|integer',
            'assignable_type' => 'required|in:user,position,department,App\\Models\\User,App\\Models\\Position,App\\Models\\Department',
            'assignable_id' => 'required|integer',
            'preset' => 'required|in:view_only,read_download,contributor,editor,full_access',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $user = Auth::user();
        $resource = null;

        if ($request->resource_type === 'file') {
            $resource = File::find($request->resource_id);
        } else {
            $resource = Folder::find($request->resource_id);
        }

        if (!$resource) {
            return response()->json(['error' => 'Resource not found'], 404);
        }

        // Check if user can manage permissions
        if (!$this->permissionService->canManagePermissions($user, $resource)) {
            return response()->json(['error' => 'Access denied. You cannot manage permissions for this resource.'], 403);
        }

        // Get assignable entity
        $assignable = null;
        switch ($request->assignable_type) {
            case 'user':
            case 'App\\Models\\User':
                $assignable = User::find($request->assignable_id);
                break;
            case 'position':
            case 'App\\Models\\Position':
                $assignable = Position::find($request->assignable_id);
                break;
            case 'department':
            case 'App\\Models\\Department':
                $assignable = Department::find($request->assignable_id);
                break;
        }

        if (!$assignable) {
            return response()->json(['error' => 'Assignable entity not found'], 404);
        }

        // Get preset permissions
        $presets = FilePermission::getPermissionPresets();
        $presetPermissions = $presets[$request->preset];

        // Assign permissions
        $permission = $this->permissionService->assignPermission(
            $resource,
            $assignable,
            $presetPermissions,
            $user,
            "Applied preset: " . ucwords(str_replace('_', ' ', $request->preset))
        );

        // If this is a folder, inherit permissions to children
        if ($resource instanceof Folder) {
            $this->permissionService->inheritPermissionsToChildren($resource);
        }

        return response()->json([
            'success' => true,
            'permission' => $permission,
            'message' => 'Permission preset applied successfully'
        ]);
    }
}
