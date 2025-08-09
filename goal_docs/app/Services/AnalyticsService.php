<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use App\Models\RecentActivity;
use App\Models\SecurityAudit;
use App\Models\WorkflowInstance;
use App\Models\Comment;
use App\Models\DocumentLock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get comprehensive analytics dashboard data
     */
    public function getDashboardAnalytics(): array
    {
        $cacheKey = 'analytics_dashboard_' . date('Y-m-d');
        
        return Cache::remember($cacheKey, 3600, function () {
            return [
                'overview' => $this->getOverviewStats(),
                'file_analytics' => $this->getFileAnalytics(),
                'user_analytics' => $this->getUserAnalytics(),
                'search_analytics' => $this->getSearchAnalytics(),
                'collaboration_analytics' => $this->getCollaborationAnalytics(),
                'workflow_analytics' => $this->getWorkflowAnalytics(),
                'security_analytics' => $this->getSecurityAnalytics(),
                'performance_analytics' => $this->getPerformanceAnalytics(),
                'trends' => $this->getTrends(),
                'top_performers' => $this->getTopPerformers(),
            ];
        });
    }

    /**
     * Get overview statistics
     */
    private function getOverviewStats(): array
    {
        $totalFiles = File::count();
        $totalUsers = User::count();
        $totalStorage = File::sum('file_size');
        $activeUsers = User::where('last_login_at', '>', now()->subDays(30))->count();
        
        // Recent activity
        $recentUploads = File::where('created_at', '>', now()->subDays(7))->count();
        $recentDownloads = RecentActivity::where('activity_type', 'file_download')
            ->where('created_at', '>', now()->subDays(7))
            ->count();
        
        // Growth metrics
        $filesGrowth = $this->calculateGrowth('files', 30);
        $usersGrowth = $this->calculateGrowth('users', 30);
        $storageGrowth = $this->calculateStorageGrowth(30);
        
        return [
            'total_files' => $totalFiles,
            'total_users' => $totalUsers,
            'total_storage_gb' => round($totalStorage / (1024 * 1024 * 1024), 2),
            'active_users' => $activeUsers,
            'recent_uploads' => $recentUploads,
            'recent_downloads' => $recentDownloads,
            'files_growth_percent' => $filesGrowth,
            'users_growth_percent' => $usersGrowth,
            'storage_growth_percent' => $storageGrowth,
        ];
    }

    /**
     * Get file analytics
     */
    private function getFileAnalytics(): array
    {
        // File types distribution
        $fileTypes = File::selectRaw('mime_type, COUNT(*) as count')
            ->groupBy('mime_type')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $this->getFileTypeName($item->mime_type),
                    'count' => $item->count,
                    'percentage' => round(($item->count / File::count()) * 100, 1)
                ];
            });

        // File size distribution
        $sizeRanges = [
            'Small (< 1MB)' => File::where('file_size', '<', 1024 * 1024)->count(),
            'Medium (1-10MB)' => File::whereBetween('file_size', [1024 * 1024, 10 * 1024 * 1024])->count(),
            'Large (10-100MB)' => File::whereBetween('file_size', [10 * 1024 * 1024, 100 * 1024 * 1024])->count(),
            'Very Large (> 100MB)' => File::where('file_size', '>', 100 * 1024 * 1024)->count(),
        ];

        // Most accessed files
        $mostAccessed = File::withCount(['activities as access_count' => function ($query) {
                $query->where('activity_type', 'file_access');
            }])
            ->orderBy('access_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($file) {
                return [
                    'name' => $file->name,
                    'access_count' => $file->access_count,
                    'size' => $file->human_size,
                    'last_accessed' => $file->last_accessed_at?->diffForHumans(),
                ];
            });

        // Upload trends (last 30 days)
        $uploadTrends = $this->getDailyTrends('files', 30);

        return [
            'file_types' => $fileTypes,
            'size_distribution' => $sizeRanges,
            'most_accessed' => $mostAccessed,
            'upload_trends' => $uploadTrends,
        ];
    }

    /**
     * Get user analytics
     */
    private function getUserAnalytics(): array
    {
        // User activity
        $activeUsers = User::where('last_login_at', '>', now()->subDays(30))->count();
        $newUsers = User::where('created_at', '>', now()->subDays(30))->count();
        $totalUsers = User::count();

        // User engagement
        $userEngagement = User::withCount(['files', 'activities'])
            ->orderBy('activities_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'files_count' => $user->files_count,
                    'activities_count' => $user->activities_count,
                    'last_active' => $user->last_login_at?->diffForHumans(),
                ];
            });

        // User registration trends
        $registrationTrends = $this->getDailyTrends('users', 30);

        // User types distribution
        $userTypes = User::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->map(function ($item) use ($totalUsers) {
                return [
                    'type' => ucfirst($item->type),
                    'count' => $item->count,
                    'percentage' => round(($item->count / $totalUsers) * 100, 1)
                ];
            });

        return [
            'active_users' => $activeUsers,
            'new_users' => $newUsers,
            'total_users' => $totalUsers,
            'engagement_rate' => round(($activeUsers / $totalUsers) * 100, 1),
            'user_engagement' => $userEngagement,
            'registration_trends' => $registrationTrends,
            'user_types' => $userTypes,
        ];
    }

    /**
     * Get search analytics
     */
    private function getSearchAnalytics(): array
    {
        // Search activity
        $totalSearches = RecentActivity::where('activity_type', 'search')->count();
        $recentSearches = RecentActivity::where('activity_type', 'search')
            ->where('created_at', '>', now()->subDays(7))
            ->count();

        // Popular search terms
        $popularSearches = RecentActivity::where('activity_type', 'search')
            ->selectRaw('metadata->>"$.search_term" as search_term, COUNT(*) as count')
            ->groupBy('search_term')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'term' => $item->search_term,
                    'count' => $item->count,
                ];
            });

        // Search success rate
        $successfulSearches = RecentActivity::where('activity_type', 'search')
            ->whereRaw('JSON_EXTRACT(metadata, "$.results_count") > 0')
            ->count();
        
        $searchSuccessRate = $totalSearches > 0 ? round(($successfulSearches / $totalSearches) * 100, 1) : 0;

        // Search trends
        $searchTrends = $this->getDailyTrends('search', 30);

        return [
            'total_searches' => $totalSearches,
            'recent_searches' => $recentSearches,
            'popular_terms' => $popularSearches,
            'success_rate' => $searchSuccessRate,
            'search_trends' => $searchTrends,
        ];
    }

    /**
     * Get collaboration analytics
     */
    private function getCollaborationAnalytics(): array
    {
        // Comments
        $totalComments = Comment::count();
        $recentComments = Comment::where('created_at', '>', now()->subDays(7))->count();
        $commentsPerFile = $totalComments > 0 ? round($totalComments / File::count(), 1) : 0;

        // Document locks
        $totalLocks = DocumentLock::count();
        $activeLocks = DocumentLock::where('expires_at', '>', now())->count();
        $lockDuration = DocumentLock::whereNotNull('expires_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, locked_at, expires_at)) as avg_duration')
            ->first();
        $avgLockDuration = round($lockDuration->avg_duration ?? 0, 1);

        // Collaboration activity
        $collaborationActivity = RecentActivity::whereIn('activity_type', ['comment_added', 'document_locked', 'document_unlocked'])
            ->where('created_at', '>', now()->subDays(7))
            ->count();

        // Most collaborative files
        $mostCollaborative = File::withCount(['comments', 'documentLocks'])
            ->orderBy('comments_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($file) {
                return [
                    'name' => $file->name,
                    'comments_count' => $file->comments_count,
                    'locks_count' => $file->document_locks_count,
                    'total_activity' => $file->comments_count + $file->document_locks_count,
                ];
            });

        return [
            'total_comments' => $totalComments,
            'recent_comments' => $recentComments,
            'comments_per_file' => $commentsPerFile,
            'total_locks' => $totalLocks,
            'active_locks' => $activeLocks,
            'avg_lock_duration_minutes' => $avgLockDuration,
            'collaboration_activity' => $collaborationActivity,
            'most_collaborative' => $mostCollaborative,
        ];
    }

    /**
     * Get workflow analytics
     */
    private function getWorkflowAnalytics(): array
    {
        // Workflow instances
        $totalInstances = WorkflowInstance::count();
        $activeInstances = WorkflowInstance::where('status', 'in_progress')->count();
        $completedInstances = WorkflowInstance::where('status', 'completed')->count();
        $rejectedInstances = WorkflowInstance::where('status', 'rejected')->count();

        // Workflow performance
        $avgCompletionTime = WorkflowInstance::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg_hours')
            ->first();
        $avgHours = round($avgCompletionTime->avg_hours ?? 0, 1);

        // Workflow success rate
        $successRate = $totalInstances > 0 ? round(($completedInstances / $totalInstances) * 100, 1) : 0;

        // Workflow trends
        $workflowTrends = $this->getDailyTrends('workflows', 30);

        return [
            'total_instances' => $totalInstances,
            'active_instances' => $activeInstances,
            'completed_instances' => $completedInstances,
            'rejected_instances' => $rejectedInstances,
            'success_rate' => $successRate,
            'avg_completion_hours' => $avgHours,
            'workflow_trends' => $workflowTrends,
        ];
    }

    /**
     * Get security analytics
     */
    private function getSecurityAnalytics(): array
    {
        // Security events
        $totalSecurityEvents = SecurityAudit::count();
        $recentSecurityEvents = SecurityAudit::where('created_at', '>', now()->subDays(7))->count();
        $failedAccessAttempts = SecurityAudit::where('action', 'access_denied')->count();

        // Security events by type
        $eventsByType = SecurityAudit::selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'action' => $this->getSecurityActionName($item->action),
                    'count' => $item->count,
                ];
            });

        // Security trends
        $securityTrends = $this->getDailyTrends('security', 30);

        return [
            'total_events' => $totalSecurityEvents,
            'recent_events' => $recentSecurityEvents,
            'failed_attempts' => $failedAccessAttempts,
            'events_by_type' => $eventsByType,
            'security_trends' => $securityTrends,
        ];
    }

    /**
     * Get performance analytics
     */
    private function getPerformanceAnalytics(): array
    {
        // Storage usage
        $totalStorage = File::sum('file_size');
        $avgFileSize = File::avg('file_size');
        $largestFile = File::orderBy('file_size', 'desc')->first();

        // File processing
        $processedFiles = File::whereNotNull('indexed_at')->count();
        $totalFiles = File::count();
        $processingRate = $totalFiles > 0 ? round(($processedFiles / $totalFiles) * 100, 1) : 0;

        // System performance
        $recentActivity = RecentActivity::where('created_at', '>', now()->subDays(1))->count();
        $avgResponseTime = 150; // Mock data - would be calculated from actual metrics

        return [
            'total_storage_gb' => round($totalStorage / (1024 * 1024 * 1024), 2),
            'avg_file_size_mb' => round($avgFileSize / (1024 * 1024), 2),
            'largest_file' => $largestFile ? [
                'name' => $largestFile->name,
                'size' => $largestFile->human_size,
            ] : null,
            'processing_rate' => $processingRate,
            'recent_activity' => $recentActivity,
            'avg_response_time_ms' => $avgResponseTime,
        ];
    }

    /**
     * Get trends data
     */
    private function getTrends(): array
    {
        return [
            'files' => $this->getDailyTrends('files', 30),
            'users' => $this->getDailyTrends('users', 30),
            'searches' => $this->getDailyTrends('search', 30),
            'collaboration' => $this->getDailyTrends('collaboration', 30),
            'workflows' => $this->getDailyTrends('workflows', 30),
            'security' => $this->getDailyTrends('security', 30),
        ];
    }

    /**
     * Get top performers
     */
    private function getTopPerformers(): array
    {
        // Top file uploaders
        $topUploaders = User::withCount('files')
            ->orderBy('files_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'files_count' => $user->files_count,
                    'type' => 'Uploader',
                ];
            });

        // Top collaborators
        $topCollaborators = User::withCount(['activities as collaboration_count' => function ($query) {
                $query->whereIn('activity_type', ['comment_added', 'document_locked']);
            }])
            ->orderBy('collaboration_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'count' => $user->collaboration_count,
                    'type' => 'Collaborator',
                ];
            });

        // Most active searchers
        $topSearchers = User::withCount(['activities as search_count' => function ($query) {
                $query->where('activity_type', 'search');
            }])
            ->orderBy('search_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'count' => $user->search_count,
                    'type' => 'Searcher',
                ];
            });

        return [
            'uploaders' => $topUploaders,
            'collaborators' => $topCollaborators,
            'searchers' => $topSearchers,
        ];
    }

    /**
     * Get daily trends for a specific metric
     */
    private function getDailyTrends(string $metric, int $days): array
    {
        $trends = [];
        $startDate = now()->subDays($days);

        switch ($metric) {
            case 'files':
                $data = File::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;
            case 'users':
                $data = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;
            case 'search':
                $data = RecentActivity::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('activity_type', 'search')
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;
            case 'collaboration':
                $data = RecentActivity::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->whereIn('activity_type', ['comment_added', 'document_locked', 'document_unlocked'])
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;
            case 'workflows':
                $data = WorkflowInstance::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;
            case 'security':
                $data = SecurityAudit::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', $startDate)
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;
            default:
                $data = collect();
        }

        // Fill in missing dates with zero counts
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $count = $data->where('date', $date)->first()->count ?? 0;
            $trends[] = [
                'date' => $date,
                'count' => $count,
            ];
        }

        return $trends;
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowth(string $metric, int $days): float
    {
        $currentPeriod = $this->getCountForPeriod($metric, $days);
        $previousPeriod = $this->getCountForPeriod($metric, $days * 2, $days);
        
        if ($previousPeriod == 0) {
            return $currentPeriod > 0 ? 100 : 0;
        }
        
        return round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 1);
    }

    /**
     * Calculate storage growth
     */
    private function calculateStorageGrowth(int $days): float
    {
        $currentStorage = File::where('created_at', '>', now()->subDays($days))->sum('file_size');
        $previousStorage = File::whereBetween('created_at', [
            now()->subDays($days * 2),
            now()->subDays($days)
        ])->sum('file_size');
        
        if ($previousStorage == 0) {
            return $currentStorage > 0 ? 100 : 0;
        }
        
        return round((($currentStorage - $previousStorage) / $previousStorage) * 100, 1);
    }

    /**
     * Get count for a specific period
     */
    private function getCountForPeriod(string $metric, int $days, int $offset = 0): int
    {
        $startDate = now()->subDays($days + $offset);
        $endDate = now()->subDays($offset);

        switch ($metric) {
            case 'files':
                return File::whereBetween('created_at', [$startDate, $endDate])->count();
            case 'users':
                return User::whereBetween('created_at', [$startDate, $endDate])->count();
            default:
                return 0;
        }
    }

    /**
     * Get file type name
     */
    private function getFileTypeName(string $mimeType): string
    {
        $types = [
            'application/pdf' => 'PDF',
            'text/plain' => 'Text',
            'application/msword' => 'Word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word',
            'application/vnd.ms-excel' => 'Excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel',
            'image/jpeg' => 'JPEG',
            'image/png' => 'PNG',
            'image/gif' => 'GIF',
        ];

        return $types[$mimeType] ?? 'Other';
    }

    /**
     * Get security action name
     */
    private function getSecurityActionName(string $action): string
    {
        $actions = [
            'file_encrypt' => 'File Encryption',
            'file_decrypt' => 'File Decryption',
            'file_watermark' => 'File Watermarking',
            'access_denied' => 'Access Denied',
            'login_success' => 'Login Success',
            'login_failed' => 'Login Failed',
        ];

        return $actions[$action] ?? ucfirst(str_replace('_', ' ', $action));
    }

    /**
     * Clear analytics cache
     */
    public function clearCache(): void
    {
        Cache::forget('analytics_dashboard_' . date('Y-m-d'));
    }
} 