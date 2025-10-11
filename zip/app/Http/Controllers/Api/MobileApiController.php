<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\File;
use App\Models\Folder;
use App\Models\SecurityLog;
use App\Models\UserSession;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MobileApiController extends Controller
{
    /**
     * Mobile API authentication endpoint.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_id' => 'required|string',
            'device_name' => 'required|string',
            'device_type' => 'required|string|in:ios,android,web',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only(['email', 'password']);
        
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('mobile-app')->plainTextToken;

            // Log mobile login
            SecurityLog::create([
                'user_id' => $user->id,
                'event_type' => 'mobile_login',
                'severity' => 'info',
                'description' => 'Mobile app login successful',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => [
                    'device_id' => $request->device_id,
                    'device_name' => $request->device_name,
                    'device_type' => $request->device_type,
                ],
            ]);

            // Create or update mobile session
            UserSession::updateOrCreate(
                ['device_id' => $request->device_id, 'user_id' => $user->id],
                [
                    'session_token' => $token,
                    'device_name' => $request->device_name,
                    'device_type' => $request->device_type,
                    'is_active' => true,
                    'is_mobile' => true,
                    'last_activity' => Carbon::now(),
                    'expires_at' => Carbon::now()->addDays(30),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_admin' => $user->is_admin,
                        'avatar' => $user->avatar,
                    ],
                    'token' => $token,
                    'expires_at' => Carbon::now()->addDays(30)->toISOString(),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
        ], 401);
    }

    /**
     * Mobile API logout endpoint.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();
        $deviceId = $request->header('X-Device-ID');

        // Log mobile logout
        SecurityLog::create([
            'user_id' => $user->id,
            'event_type' => 'mobile_logout',
            'severity' => 'info',
            'description' => 'Mobile app logout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'device_id' => $deviceId,
            ],
        ]);

        // Deactivate mobile session
        if ($deviceId) {
            UserSession::where('device_id', $deviceId)
                ->where('user_id', $user->id)
                ->update([
                    'is_active' => false,
                    'last_activity' => Carbon::now(),
                ]);
        }

        // Revoke token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get user profile for mobile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'avatar' => $user->avatar,
                    'created_at' => $user->created_at->toISOString(),
                    'last_login' => $user->last_login_at?->toISOString(),
                ],
                'stats' => [
                    'total_files' => File::where('user_id', $user->id)->count(),
                    'total_folders' => Folder::where('user_id', $user->id)->count(),
                    'storage_used' => $this->getUserStorageUsed($user->id),
                ],
            ],
        ]);
    }

    /**
     * Get files for mobile with pagination and filtering.
     */
    public function files(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);
        $search = $request->get('search');
        $folderId = $request->get('folder_id');
        $type = $request->get('type');

        $query = File::where('user_id', $user->id);

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($folderId) {
            $query->where('folder_id', $folderId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $files = $query->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'files' => $files->items(),
                'pagination' => [
                    'current_page' => $files->currentPage(),
                    'last_page' => $files->lastPage(),
                    'per_page' => $files->perPage(),
                    'total' => $files->total(),
                ],
            ],
        ]);
    }

    /**
     * Get folders for mobile.
     */
    public function folders(Request $request): JsonResponse
    {
        $user = Auth::user();
        $parentId = $request->get('parent_id');

        $query = Folder::where('user_id', $user->id);

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        $folders = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'folders' => $folders,
            ],
        ]);
    }

    /**
     * Get file details for mobile.
     */
    public function fileDetails($fileId): JsonResponse
    {
        $user = Auth::user();
        $file = File::where('id', $fileId)
            ->where('user_id', $user->id)
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'file' => $file,
                'download_url' => route('api.mobile.download', $file->id),
                'preview_url' => route('api.mobile.preview', $file->id),
            ],
        ]);
    }

    /**
     * Download file for mobile.
     */
    public function download($fileId): JsonResponse
    {
        $user = Auth::user();
        $file = File::where('id', $fileId)
            ->where('user_id', $user->id)
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        if (!Storage::exists($file->path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found on server',
            ], 404);
        }

        // Log download
        SecurityLog::create([
            'user_id' => $user->id,
            'event_type' => 'mobile_download',
            'severity' => 'info',
            'description' => "Mobile download: {$file->name}",
            'metadata' => [
                'file_id' => $file->id,
                'file_name' => $file->name,
                'file_size' => $file->size,
            ],
        ]);

        $url = Storage::temporaryUrl(
            $file->path,
            Carbon::now()->addMinutes(30),
            [
                'ResponseContentDisposition' => 'attachment; filename="' . $file->name . '"',
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'download_url' => $url,
                'expires_at' => Carbon::now()->addMinutes(30)->toISOString(),
            ],
        ]);
    }

    /**
     * Get file preview for mobile.
     */
    public function preview($fileId): JsonResponse
    {
        $user = Auth::user();
        $file = File::where('id', $fileId)
            ->where('user_id', $user->id)
            ->first();

        if (!$file) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        // Log preview
        SecurityLog::create([
            'user_id' => $user->id,
            'event_type' => 'mobile_preview',
            'severity' => 'info',
            'description' => "Mobile preview: {$file->name}",
            'metadata' => [
                'file_id' => $file->id,
                'file_name' => $file->name,
            ],
        ]);

        $url = Storage::temporaryUrl(
            $file->path,
            Carbon::now()->addMinutes(60)
        );

        return response()->json([
            'success' => true,
            'data' => [
                'preview_url' => $url,
                'expires_at' => Carbon::now()->addMinutes(60)->toISOString(),
            ],
        ]);
    }

    /**
     * Upload file from mobile.
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:100000', // 100MB max
            'folder_id' => 'nullable|exists:folders,id',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $uploadedFile = $request->file('file');
        $fileName = $request->get('name') ?: $uploadedFile->getClientOriginalName();
        $folderId = $request->get('folder_id');

        // Check folder permissions
        if ($folderId) {
            $folder = Folder::where('id', $folderId)
                ->where('user_id', $user->id)
                ->first();

            if (!$folder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder not found or access denied',
                ], 403);
            }
        }

        try {
            $path = $uploadedFile->store('files/' . $user->id, 'private');

            $file = File::create([
                'name' => $fileName,
                'original_name' => $uploadedFile->getClientOriginalName(),
                'path' => $path,
                'size' => $uploadedFile->getSize(),
                'type' => $uploadedFile->getMimeType(),
                'extension' => $uploadedFile->getClientOriginalExtension(),
                'user_id' => $user->id,
                'folder_id' => $folderId,
                'uploaded_via' => 'mobile',
            ]);

            // Log upload
            SecurityLog::create([
                'user_id' => $user->id,
                'event_type' => 'mobile_upload',
                'severity' => 'info',
                'description' => "Mobile upload: {$fileName}",
                'metadata' => [
                    'file_id' => $file->id,
                    'file_name' => $fileName,
                    'file_size' => $uploadedFile->getSize(),
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'file' => $file,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create folder from mobile.
     */
    public function createFolder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $name = $request->get('name');
        $parentId = $request->get('parent_id');

        // Check parent folder permissions
        if ($parentId) {
            $parentFolder = Folder::where('id', $parentId)
                ->where('user_id', $user->id)
                ->first();

            if (!$parentFolder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent folder not found or access denied',
                ], 403);
            }
        }

        // Check if folder already exists
        $existingFolder = Folder::where('name', $name)
            ->where('user_id', $user->id)
            ->where('parent_id', $parentId)
            ->first();

        if ($existingFolder) {
            return response()->json([
                'success' => false,
                'message' => 'Folder already exists',
            ], 409);
        }

        $folder = Folder::create([
            'name' => $name,
            'user_id' => $user->id,
            'parent_id' => $parentId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Folder created successfully',
            'data' => [
                'folder' => $folder,
            ],
        ]);
    }

    /**
     * Search files and folders for mobile.
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'type' => 'nullable|string|in:files,folders,all',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::user();
        $query = $request->get('query');
        $type = $request->get('type', 'all');

        $results = [];

        if ($type === 'files' || $type === 'all') {
            $files = File::where('user_id', $user->id)
                ->where('name', 'like', "%{$query}%")
                ->orderBy('updated_at', 'desc')
                ->limit(20)
                ->get();

            $results['files'] = $files;
        }

        if ($type === 'folders' || $type === 'all') {
            $folders = Folder::where('user_id', $user->id)
                ->where('name', 'like', "%{$query}%")
                ->orderBy('name')
                ->limit(20)
                ->get();

            $results['folders'] = $folders;
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    /**
     * Get mobile app statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = Auth::user();

        $stats = [
            'files' => [
                'total' => File::where('user_id', $user->id)->count(),
                'recent' => File::where('user_id', $user->id)
                    ->where('created_at', '>=', Carbon::now()->subDays(7))
                    ->count(),
                'storage_used' => $this->getUserStorageUsed($user->id),
            ],
            'folders' => [
                'total' => Folder::where('user_id', $user->id)->count(),
            ],
            'activity' => [
                'recent_uploads' => SecurityLog::where('user_id', $user->id)
                    ->where('event_type', 'mobile_upload')
                    ->where('created_at', '>=', Carbon::now()->subDays(7))
                    ->count(),
                'recent_downloads' => SecurityLog::where('user_id', $user->id)
                    ->where('event_type', 'mobile_download')
                    ->where('created_at', '>=', Carbon::now()->subDays(7))
                    ->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get user storage usage.
     */
    private function getUserStorageUsed($userId): int
    {
        return File::where('user_id', $userId)->sum('size');
    }

    /**
     * Refresh mobile session.
     */
    public function refreshSession(Request $request): JsonResponse
    {
        $user = Auth::user();
        $deviceId = $request->header('X-Device-ID');

        if ($deviceId) {
            UserSession::where('device_id', $deviceId)
                ->where('user_id', $user->id)
                ->update([
                    'last_activity' => Carbon::now(),
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Session refreshed',
            'data' => [
                'refreshed_at' => Carbon::now()->toISOString(),
            ],
        ]);
    }
}
