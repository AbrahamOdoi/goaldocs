<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\FileTag;
use App\Models\UserFavorite;
use App\Models\RecentActivity;
use App\Services\PermissionService;
use App\Services\DocumentProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdvancedSearchController extends Controller
{
    protected $permissionService;
    protected $documentProcessingService;

    public function __construct(PermissionService $permissionService, DocumentProcessingService $documentProcessingService)
    {
        $this->permissionService = $permissionService;
        $this->documentProcessingService = $documentProcessingService;
    }

    /**
     * Show the advanced search page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = $request->get('q', '');
        $filters = $request->only([
            'type', 'date_from', 'date_to', 'size_min', 'size_max', 
            'tags', 'mime_type', 'uploaded_by', 'folder_id', 'search_in_content'
        ]);
        
        $results = collect();
        $searchStats = [];
        
        if ($query || !empty(array_filter($filters))) {
            $results = $this->performAdvancedSearch($user, $query, $filters);
            $searchStats = $this->getSearchStats($user, $query, $filters);
        }

        // Get recent activities
        $recentActivities = RecentActivity::getRecentForUser(
            $user->id, 
            $user->type, 
            $user->type_name, 
            10
        );

        // Get user's favorites
        $favorites = UserFavorite::forUser($user->id)
            ->forOrganization($user->type, $user->type_name)
            ->with(['file', 'folder'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get popular tags
        $popularTags = FileTag::getPopularTags($user->type, $user->type_name, 15);

        // Get search suggestions
        $suggestions = $this->getSearchSuggestions($user, $query);

        // Get file type statistics
        $fileTypeStats = $this->getFileTypeStats($user);

        // Log search activity if there's a query or filters
        if ($query || !empty(array_filter($filters))) {
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'search',
                null,
                null,
                [
                    'search_query' => $query, 
                    'filters' => $filters, 
                    'results_count' => $results->count(),
                    'search_type' => 'advanced'
                ]
            );
        }

        return view('search.advanced', compact(
            'results', 
            'query', 
            'filters', 
            'recentActivities', 
            'favorites', 
            'popularTags',
            'suggestions',
            'searchStats',
            'fileTypeStats'
        ));
    }

    /**
     * Perform advanced search with full-text capabilities
     */
    private function performAdvancedSearch($user, $query, $filters)
    {
        $results = collect();

        // Search files with full-text search
        $filesQuery = File::forOrganization($user->type, $user->type_name)
            ->active()
            ->with(['folder', 'tags', 'uploader']);

        // Apply full-text search if query exists
        if ($query) {
            if (isset($filters['search_in_content']) && $filters['search_in_content']) {
                // Search within document content
                $filesQuery->searchInContent($query);
            } else {
                // Full-text search across all fields
                $filesQuery->fullTextSearch($query);
            }
        }

        // Apply filters
        $filesQuery = $this->applyAdvancedFilters($filesQuery, $filters);

        // Get files and filter by permissions
        $accessibleFiles = $filesQuery->get()->filter(function($file) use ($user) {
            return $this->permissionService->userHasPermission($user, $file, 'view');
        });

        // Add search relevance scores
        $scoredFiles = $accessibleFiles->map(function($file) use ($query) {
            $file->search_relevance_score = $file->getSearchRelevanceScore($query);
            $file->resource_type = 'file';
            return $file;
        });

        // Sort by relevance score
        $scoredFiles = $scoredFiles->sortByDesc('search_relevance_score');

        $results = $results->merge($scoredFiles);

        // Search folders
        $foldersQuery = Folder::forOrganization($user->type, $user->type_name)
            ->active()
            ->with(['parent', 'creator']);

        if ($query) {
            $foldersQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            });
        }

        // Apply folder filters
        $foldersQuery = $this->applyFolderFilters($foldersQuery, $filters);

        $accessibleFolders = $foldersQuery->get()->filter(function($folder) use ($user) {
            return $this->permissionService->userHasPermission($user, $folder, 'view');
        });

        $scoredFolders = $accessibleFolders->map(function($folder) use ($query) {
            $folder->search_relevance_score = $folder->getSearchRelevanceScore($query);
            $folder->resource_type = 'folder';
            return $folder;
        });

        $results = $results->merge($scoredFolders);

        return $results;
    }

    /**
     * Apply advanced filters to file query
     */
    private function applyAdvancedFilters($query, $filters)
    {
        // File type filter
        if (!empty($filters['type'])) {
            switch ($filters['type']) {
                case 'images':
                    $query->where('mime_type', 'like', 'image/%');
                    break;
                case 'documents':
                    $query->whereIn('mime_type', [
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'text/plain'
                    ]);
                    break;
                case 'videos':
                    $query->where('mime_type', 'like', 'video/%');
                    break;
                case 'audio':
                    $query->where('mime_type', 'like', 'audio/%');
                    break;
                case 'archives':
                    $query->whereIn('mime_type', [
                        'application/zip',
                        'application/x-rar-compressed',
                        'application/x-7z-compressed'
                    ]);
                    break;
            }
        }

        // Specific MIME type filter
        if (!empty($filters['mime_type'])) {
            $query->where('mime_type', $filters['mime_type']);
        }

        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Size range filter
        if (!empty($filters['size_min'])) {
            $query->where('file_size', '>=', $filters['size_min'] * 1024 * 1024); // Convert MB to bytes
        }
        if (!empty($filters['size_max'])) {
            $query->where('file_size', '<=', $filters['size_max'] * 1024 * 1024);
        }

        // Uploader filter
        if (!empty($filters['uploaded_by'])) {
            $query->where('uploaded_by', $filters['uploaded_by']);
        }

        // Folder filter
        if (!empty($filters['folder_id'])) {
            $query->where('folder_id', $filters['folder_id']);
        }

        // Tags filter
        if (!empty($filters['tags'])) {
            $tagIds = explode(',', $filters['tags']);
            $query->whereHas('tags', function($q) use ($tagIds) {
                $q->whereIn('id', $tagIds);
            });
        }

        return $query;
    }

    /**
     * Apply filters to folder query
     */
    private function applyFolderFilters($query, $filters)
    {
        // Date range filter
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Creator filter
        if (!empty($filters['uploaded_by'])) {
            $query->where('created_by', $filters['uploaded_by']);
        }

        return $query;
    }

    /**
     * Get search statistics
     */
    private function getSearchStats($user, $query, $filters)
    {
        $cacheKey = "search_stats_{$user->id}_" . md5($query . serialize($filters));
        
        return Cache::remember($cacheKey, 300, function() use ($user, $query, $filters) {
            $stats = [
                'total_files' => 0,
                'total_folders' => 0,
                'file_types' => [],
                'size_distribution' => [],
                'date_distribution' => []
            ];

            // Base query used for all stats; clone for each operation to avoid cross-mutation
            $baseQuery = File::forOrganization($user->type, $user->type_name)->active();
            if ($query) {
                // Use full text search (or content search if filter present)
                if (!empty($filters['search_in_content'])) {
                    $baseQuery->searchInContent($query);
                } else {
                    $baseQuery->fullTextSearch($query);
                }
            }
            $baseQuery = $this->applyAdvancedFilters($baseQuery, $filters);

            // Total files
            $stats['total_files'] = (clone $baseQuery)->count();

            // File type distribution
            $stats['file_types'] = (clone $baseQuery)
                ->select('mime_type', DB::raw('count(*) as total'))
                ->groupBy('mime_type')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get()
                ->pluck('total', 'mime_type')
                ->toArray();

            // Size distribution
            $stats['size_distribution'] = [
                'small' => (clone $baseQuery)->where('file_size', '<', 1024 * 1024)->count(), // < 1MB
                'medium' => (clone $baseQuery)->whereBetween('file_size', [1024 * 1024, 10 * 1024 * 1024])->count(), // 1-10MB
                'large' => (clone $baseQuery)->where('file_size', '>', 10 * 1024 * 1024)->count(), // > 10MB
            ];

            return $stats;
        });
    }

    /**
     * Get search suggestions
     */
    private function getSearchSuggestions($user, $query)
    {
        if (empty($query) || strlen($query) < 2) {
            return collect();
        }

        $cacheKey = "search_suggestions_{$user->id}_" . md5($query);
        
        return Cache::remember($cacheKey, 600, function() use ($user, $query) {
            $suggestions = collect();

            // Get file name suggestions
            $fileNames = File::forOrganization($user->type, $user->type_name)
                ->active()
                ->where('name', 'like', "%{$query}%")
                ->select('name')
                ->distinct()
                ->limit(5)
                ->pluck('name');

            $suggestions = $suggestions->merge($fileNames);

            // Get tag suggestions
            $tagSuggestions = FileTag::getTagSuggestions(
                $user->type, 
                $user->type_name, 
                $query, 
                5
            );

            $suggestions = $suggestions->merge($tagSuggestions);

            return $suggestions->unique()->take(10);
        });
    }

    /**
     * Get file type statistics
     */
    private function getFileTypeStats($user)
    {
        $cacheKey = "file_type_stats_{$user->id}";
        
        return Cache::remember($cacheKey, 3600, function() use ($user) {
            return File::forOrganization($user->type, $user->type_name)
                ->active()
                ->select('mime_type', DB::raw('count(*) as count'))
                ->groupBy('mime_type')
                ->orderBy('count', 'desc')
                ->limit(15)
                ->get();
        });
    }

    /**
     * AJAX endpoint for real-time search
     */
    public function ajaxSearch(Request $request)
    {
        $user = Auth::user();
        $query = $request->get('q', '');
        $filters = $request->only(['type', 'search_in_content']);
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json(['results' => [], 'suggestions' => []]);
        }

        $results = $this->performAdvancedSearch($user, $query, $filters);
        $suggestions = $this->getSearchSuggestions($user, $query);

        return response()->json([
            'results' => $results->take(10)->values(),
            'suggestions' => $suggestions->take(5)->values(),
            'total_count' => $results->count()
        ]);
    }

    /**
     * Get search analytics
     */
    public function analytics(Request $request)
    {
        $user = Auth::user();
        
        // Get search history
        $searchHistory = RecentActivity::where('user_id', $user->id)
            ->where('action', 'search')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // Get popular searches
        $popularSearches = RecentActivity::where('action', 'search')
            ->where('user_type', $user->type)
            ->where('type_name', $user->type_name)
            ->whereNotNull('metadata->search_query')
            ->select('metadata->search_query as query', DB::raw('count(*) as count'))
            ->groupBy('query')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'search_history' => $searchHistory,
            'popular_searches' => $popularSearches
        ]);
    }
} 