<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\FileTag;
use App\Models\UserFavorite;
use App\Models\RecentActivity;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Show the search page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $request->get('q', '');
        $filters = $request->only(['type', 'date_from', 'date_to', 'size_min', 'size_max', 'tags']);
        
        $results = collect();
        
        if ($query || !empty(array_filter($filters))) {
            $results = $this->performSearch($user, $query, $filters);
        }

        // Get recent activities
        $recentActivities = RecentActivity::getRecentForUser(
            $user->id, 
            $user->user_type, 
            $user->type_name, 
            10
        );

        // Get user's favorites
        $favorites = UserFavorite::forUser($user->id)
            ->forOrganization($user->user_type, $user->type_name)
            ->with(['file', 'folder'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get popular tags
        $popularTags = FileTag::getPopularTags($user->user_type, $user->type_name, 15);

        return view('search.index', compact('results', 'query', 'filters', 'recentActivities', 'favorites', 'popularTags'));
    }

    /**
     * Perform search across files and folders
     */
    private function performSearch($user, $query, $filters)
    {
        $results = collect();

        // Search files
        $files = File::forOrganization($user->user_type, $user->type_name)
            ->active()
            ->with(['folder', 'tags', 'uploader']);

        if ($query) {
            $files->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('original_name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }

        // Apply filters
        $files = $this->applyFilters($files, $filters, 'files');

        // Filter by permissions
        $accessibleFiles = $files->get()->filter(function($file) use ($user) {
            return $this->permissionService->userHasPermission($user, $file, 'view');
        });

        $results = $results->merge($accessibleFiles->map(function($file) {
            $file->resource_type = 'file';
            return $file;
        }));

        // Search folders
        $folders = Folder::forOrganization($user->user_type, $user->type_name)
            ->active()
            ->with(['parent', 'creator']);

        if ($query) {
            $folders->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }

        // Apply filters
        $folders = $this->applyFilters($folders, $filters, 'folders');

        // Filter by permissions
        $accessibleFolders = $folders->get()->filter(function($folder) use ($user) {
            return $this->permissionService->userHasPermission($user, $folder, 'view');
        });

        $results = $results->merge($accessibleFolders->map(function($folder) {
            $folder->resource_type = 'folder';
            return $folder;
        }));

        // Sort by relevance/date
        return $results->sortByDesc('created_at');
    }

    /**
     * Apply filters to search query
     */
    private function applyFilters($query, $filters, $type)
    {
        if (!empty($filters['type'])) {
            if ($type === 'files') {
                $query->where('mime_type', 'like', $filters['type'] . '%');
            }
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['size_min'])) {
            if ($type === 'files') {
                $query->where('file_size', '>=', $filters['size_min'] * 1024 * 1024); // Convert MB to bytes
            }
        }

        if (!empty($filters['size_max'])) {
            if ($type === 'files') {
                $query->where('file_size', '<=', $filters['size_max'] * 1024 * 1024); // Convert MB to bytes
            }
        }

        if (!empty($filters['tags'])) {
            $tagNames = explode(',', $filters['tags']);
            if ($type === 'files') {
                $query->whereHas('tags', function($q) use ($tagNames) {
                    $q->whereIn('tag_name', $tagNames);
                });
            }
        }

        return $query;
    }

    /**
     * Get tag suggestions for autocomplete
     */
    public function getTagSuggestions(Request $request)
    {
        $user = Auth::user();
        $query = $request->get('q', '');

        $suggestions = FileTag::getTagSuggestions(
            $user->user_type, 
            $user->type_name, 
            $query, 
            10
        );

        return response()->json($suggestions);
    }

    /**
     * Add tag to file
     */
    public function addTag(Request $request)
    {
        $request->validate([
            'file_id' => 'required|exists:files,id',
            'tag_name' => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $user = Auth::user();
        $file = File::findOrFail($request->file_id);

        // Check if user has permission to tag this file
        if (!$this->permissionService->userHasPermission($user, $file, 'edit')) {
            return response()->json(['error' => 'You do not have permission to tag this file'], 403);
        }

        // Check if tag already exists
        $existingTag = FileTag::where('file_id', $file->id)
            ->where('tag_name', $request->tag_name)
            ->first();

        if ($existingTag) {
            return response()->json(['error' => 'Tag already exists on this file'], 422);
        }

        // Create tag
        $tag = FileTag::create([
            'file_id' => $file->id,
            'tag_name' => $request->tag_name,
            'color' => $request->color ?? '#667eea',
            'user_type' => $user->user_type,
            'type_name' => $user->type_name,
            'created_by' => $user->id,
        ]);

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->user_type,
            $user->type_name,
            'tag',
            $file->id,
            null,
            ['tag_name' => $request->tag_name]
        );

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'message' => 'Tag added successfully'
        ]);
    }

    /**
     * Remove tag from file
     */
    public function removeTag(Request $request)
    {
        $request->validate([
            'tag_id' => 'required|exists:file_tags,id',
        ]);

        $user = Auth::user();
        $tag = FileTag::with('file')->findOrFail($request->tag_id);

        // Check if user has permission to remove tag
        if (!$this->permissionService->userHasPermission($user, $tag->file, 'edit')) {
            return response()->json(['error' => 'You do not have permission to remove tags from this file'], 403);
        }

        $tagName = $tag->tag_name;
        $fileId = $tag->file_id;

        $tag->delete();

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->user_type,
            $user->type_name,
            'tag',
            $fileId,
            null,
            ['tag_name' => $tagName, 'action' => 'removed']
        );

        return response()->json([
            'success' => true,
            'message' => 'Tag removed successfully'
        ]);
    }

    /**
     * Toggle favorite status
     */
    public function toggleFavorite(Request $request)
    {
        $request->validate([
            'file_id' => 'nullable|exists:files,id',
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $user = Auth::user();
        $fileId = $request->file_id;
        $folderId = $request->folder_id;

        // Ensure only one is provided
        if (!$fileId && !$folderId) {
            return response()->json(['error' => 'Either file_id or folder_id must be provided'], 422);
        }

        if ($fileId && $folderId) {
            return response()->json(['error' => 'Only one of file_id or folder_id can be provided'], 422);
        }

        // Check permissions
        if ($fileId) {
            $file = File::findOrFail($fileId);
            if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
                return response()->json(['error' => 'You do not have permission to favorite this file'], 403);
            }
        } else {
            $folder = Folder::findOrFail($folderId);
            if (!$this->permissionService->userHasPermission($user, $folder, 'view')) {
                return response()->json(['error' => 'You do not have permission to favorite this folder'], 403);
            }
        }

        // Toggle favorite
        $isFavorited = UserFavorite::toggleFavorite(
            $user->id,
            $user->user_type,
            $user->type_name,
            $fileId,
            $folderId
        );

        // Log activity
        RecentActivity::log(
            $user->id,
            $user->user_type,
            $user->type_name,
            'favorite',
            $fileId,
            $folderId,
            ['action' => $isFavorited ? 'added' : 'removed']
        );

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
            'message' => $isFavorited ? 'Added to favorites' : 'Removed from favorites'
        ]);
    }

    /**
     * Get user's favorites
     */
    public function getFavorites()
    {
        $user = Auth::user();

        $favorites = UserFavorite::forUser($user->id)
            ->forOrganization($user->user_type, $user->type_name)
            ->with(['file', 'folder'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($favorites);
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities()
    {
        $user = Auth::user();

        $activities = RecentActivity::getRecentForUser(
            $user->id,
            $user->user_type,
            $user->type_name,
            20
        );

        return response()->json($activities);
    }

    /**
     * Get popular tags
     */
    public function getPopularTags()
    {
        $user = Auth::user();

        $tags = FileTag::getPopularTags($user->user_type, $user->type_name, 15);

        return response()->json($tags);
    }
}
