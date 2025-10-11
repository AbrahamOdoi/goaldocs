<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use App\Models\RecentActivity;
use App\Models\BatchJob;
use App\Models\OcrResult;
use App\Models\DocumentConversion;
use App\Models\TextExtraction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get document usage analytics
     */
    public function getDocumentUsageAnalytics($userId = null, $period = '30d'): array
    {
        $cacheKey = "doc_usage_analytics_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $period) {
            $startDate = $this->getStartDate($period);
            
            $query = RecentActivity::where('created_at', '>=', $startDate);
            
            if ($userId) {
                $query->where('user_id', $userId);
            }
            
            $activities = $query->get();
            
            return [
                'total_activities' => $activities->count(),
                'file_views' => $activities->where('activity_type', 'view')->count(),
                'file_downloads' => $activities->where('activity_type', 'download')->count(),
                'file_uploads' => $activities->where('activity_type', 'upload')->count(),
                'file_shares' => $activities->where('activity_type', 'share')->count(),
                'daily_activity' => $this->getDailyActivityData($activities),
                'top_files' => $this->getTopFiles($userId, $period),
                'user_engagement' => $this->getUserEngagement($userId, $period),
            ];
        });
    }

    /**
     * Get processing analytics (OCR, conversion, batch)
     */
    public function getProcessingAnalytics($userId = null, $period = '30d'): array
    {
        $cacheKey = "processing_analytics_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $period) {
            $startDate = $this->getStartDate($period);
            
            // OCR Analytics
            $ocrQuery = OcrResult::where('created_at', '>=', $startDate);
            if ($userId) {
                $ocrQuery->whereHas('file', function ($q) use ($userId) {
                    $q->where('uploaded_by', $userId);
                });
            }
            $ocrResults = $ocrQuery->get();
            
            // Conversion Analytics
            $conversionQuery = DocumentConversion::where('created_at', '>=', $startDate);
            if ($userId) {
                $conversionQuery->whereHas('file', function ($q) use ($userId) {
                    $q->where('uploaded_by', $userId);
                });
            }
            $conversions = $conversionQuery->get();
            
            // Batch Processing Analytics
            $batchQuery = BatchJob::where('created_at', '>=', $startDate);
            if ($userId) {
                $batchQuery->where('user_id', $userId);
            }
            $batchJobs = $batchQuery->get();
            
            // Text Extraction Analytics
            $extractionQuery = TextExtraction::where('created_at', '>=', $startDate);
            if ($userId) {
                $extractionQuery->whereHas('file', function ($q) use ($userId) {
                    $q->where('uploaded_by', $userId);
                });
            }
            $extractions = $extractionQuery->get();
            
            return [
                'ocr' => [
                    'total_processed' => $ocrResults->count(),
                    'successful' => $ocrResults->where('status', 'completed')->count(),
                    'failed' => $ocrResults->where('status', 'failed')->count(),
                    'average_confidence' => $ocrResults->where('status', 'completed')->avg('confidence_score'),
                    'average_processing_time' => $ocrResults->where('status', 'completed')->avg('processing_time'),
                    'language_distribution' => $this->getLanguageDistribution($ocrResults),
                ],
                'conversion' => [
                    'total_conversions' => $conversions->count(),
                    'successful' => $conversions->where('status', 'completed')->count(),
                    'failed' => $conversions->where('status', 'failed')->count(),
                    'format_distribution' => $this->getFormatDistribution($conversions),
                    'average_processing_time' => $conversions->where('status', 'completed')->avg('processing_time'),
                    'quality_scores' => $this->getQualityScores($conversions),
                ],
                'batch_processing' => [
                    'total_jobs' => $batchJobs->count(),
                    'completed' => $batchJobs->whereIn('status', ['completed', 'completed_with_errors'])->count(),
                    'processing' => $batchJobs->where('status', 'processing')->count(),
                    'failed' => $batchJobs->where('status', 'failed')->count(),
                    'total_files_processed' => $batchJobs->sum('total_files'),
                    'successful_files' => $batchJobs->sum('successful_files'),
                    'failed_files' => $batchJobs->sum('failed_files'),
                    'operation_distribution' => $this->getOperationDistribution($batchJobs),
                ],
                'text_extraction' => [
                    'total_extractions' => $extractions->count(),
                    'successful' => $extractions->where('status', 'completed')->count(),
                    'failed' => $extractions->where('status', 'failed')->count(),
                    'average_processing_time' => $extractions->where('status', 'completed')->avg('processing_time'),
                    'extraction_types' => $this->getExtractionTypeDistribution($extractions),
                    'quality_scores' => $this->getExtractionQualityScores($extractions),
                ],
            ];
        });
    }

    /**
     * Get storage analytics
     */
    public function getStorageAnalytics($userId = null, $period = '30d'): array
    {
        $cacheKey = "storage_analytics_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $period) {
            $startDate = $this->getStartDate($period);
            
            $fileQuery = File::where('created_at', '>=', $startDate);
            if ($userId) {
                $fileQuery->where('uploaded_by', $userId);
            }
            $files = $fileQuery->get();
            
            $totalSize = $files->sum('file_size');
            $fileCount = $files->count();
            
            return [
                'total_storage_used' => $totalSize,
                'formatted_storage' => $this->formatBytes($totalSize),
                'total_files' => $fileCount,
                'average_file_size' => $fileCount > 0 ? $totalSize / $fileCount : 0,
                'file_type_distribution' => $this->getFileTypeDistribution($files),
                'storage_growth' => $this->getStorageGrowth($userId, $period),
                'largest_files' => $this->getLargestFiles($userId, 10),
                'storage_by_user_type' => $this->getStorageByUserType($userId),
            ];
        });
    }

    /**
     * Get user activity analytics
     */
    public function getUserActivityAnalytics($userId = null, $period = '30d'): array
    {
        $cacheKey = "user_activity_analytics_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $period) {
            $startDate = $this->getStartDate($period);
            
            $query = RecentActivity::where('created_at', '>=', $startDate);
            if ($userId) {
                $query->where('user_id', $userId);
            }
            $activities = $query->get();
            
            return [
                'total_activities' => $activities->count(),
                'active_users' => $activities->unique('user_id')->count(),
                'activity_by_type' => $this->getActivityByType($activities),
                'peak_activity_hours' => $this->getPeakActivityHours($activities),
                'activity_trends' => $this->getActivityTrends($activities),
                'user_productivity' => $this->getUserProductivity($userId, $period),
                'collaboration_metrics' => $this->getCollaborationMetrics($userId, $period),
            ];
        });
    }

    /**
     * Get comprehensive dashboard analytics
     */
    public function getDashboardAnalytics($userId = null): array
    {
        $cacheKey = "dashboard_analytics_{$userId}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId) {
            return [
                'document_usage' => $this->getDocumentUsageAnalytics($userId, '7d'),
                'processing' => $this->getProcessingAnalytics($userId, '7d'),
                'storage' => $this->getStorageAnalytics($userId, '7d'),
                'user_activity' => $this->getUserActivityAnalytics($userId, '7d'),
                'quick_stats' => $this->getQuickStats($userId),
                'recent_activity' => $this->getRecentActivity($userId, 10),
                'system_health' => $this->getSystemHealth(),
            ];
        });
    }

    /**
     * Get quick stats for dashboard
     */
    private function getQuickStats($userId = null): array
    {
        $today = Carbon::today();
        
        $fileQuery = File::whereDate('created_at', $today);
        $activityQuery = RecentActivity::whereDate('created_at', $today);
        $batchQuery = BatchJob::whereDate('created_at', $today);
        
        if ($userId) {
            $fileQuery->where('uploaded_by', $userId);
            $activityQuery->where('user_id', $userId);
            $batchQuery->where('user_id', $userId);
        }
        
        return [
            'files_uploaded_today' => $fileQuery->count(),
            'activities_today' => $activityQuery->count(),
            'batch_jobs_today' => $batchQuery->count(),
            'storage_used_today' => $fileQuery->sum('file_size'),
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity($userId = null, $limit = 10): array
    {
        $query = RecentActivity::with(['user', 'file'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);
            
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->get()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'user_name' => $activity->user->name ?? 'Unknown',
                'activity_type' => $activity->activity_type,
                'file_name' => $activity->file->name ?? 'N/A',
                'created_at' => $activity->created_at->format('M j, Y g:i A'),
                'description' => $this->getActivityDescription($activity),
            ];
        })->toArray();
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealth(): array
    {
        return [
            'database_connections' => DB::connection()->getPdo() ? 'Healthy' : 'Unhealthy',
            'cache_status' => Cache::has('health_check') ? 'Healthy' : 'Unhealthy',
            'storage_available' => $this->getStorageAvailable(),
            'active_users' => User::where('last_seen_at', '>=', now()->subMinutes(5))->count(),
            'processing_queue' => BatchJob::where('status', 'processing')->count(),
        ];
    }

    /**
     * Helper methods for data processing
     */
    private function getStartDate($period): Carbon
    {
        return match ($period) {
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y' => now()->subYear(),
            default => now()->subDays(30),
        };
    }

    private function getDailyActivityData($activities): array
    {
        $dailyData = [];
        $startDate = now()->subDays(30);
        
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $dailyData[$date] = $activities->filter(function ($activity) use ($date) {
                return $activity->created_at->format('Y-m-d') === $date;
            })->count();
        }
        
        return $dailyData;
    }

    private function getTopFiles($userId = null, $period = '30d'): array
    {
        $startDate = $this->getStartDate($period);
        
        $query = RecentActivity::with('file')
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('file_id')
            ->select('file_id', DB::raw('count(*) as activity_count'))
            ->groupBy('file_id')
            ->orderBy('activity_count', 'desc')
            ->limit(10);
            
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->get()->map(function ($item) {
            return [
                'file_name' => $item->file->name ?? 'Unknown',
                'activity_count' => $item->activity_count,
                'file_size' => $item->file->file_size ?? 0,
            ];
        })->toArray();
    }

    private function getUserEngagement($userId = null, $period = '30d'): array
    {
        $startDate = $this->getStartDate($period);
        
        $query = RecentActivity::where('created_at', '>=', $startDate)
            ->select('user_id', DB::raw('count(*) as activity_count'))
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit(10);
            
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        return $query->get()->map(function ($item) {
            $user = User::find($item->user_id);
            return [
                'user_name' => $user->name ?? 'Unknown',
                'activity_count' => $item->activity_count,
            ];
        })->toArray();
    }

    private function getLanguageDistribution($ocrResults): array
    {
        return $ocrResults->groupBy('language')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();
    }

    private function getFormatDistribution($conversions): array
    {
        return $conversions->groupBy('target_format')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();
    }

    private function getQualityScores($conversions): array
    {
        return [
            'average' => $conversions->where('status', 'completed')->avg('quality_score'),
            'high' => $conversions->where('status', 'completed')->where('quality_score', '>=', 0.8)->count(),
            'medium' => $conversions->where('status', 'completed')->whereBetween('quality_score', [0.6, 0.79])->count(),
            'low' => $conversions->where('status', 'completed')->where('quality_score', '<', 0.6)->count(),
        ];
    }

    private function getOperationDistribution($batchJobs): array
    {
        return $batchJobs->groupBy('operation_type')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();
    }

    private function getExtractionTypeDistribution($extractions): array
    {
        return $extractions->groupBy('extraction_type')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();
    }

    private function getExtractionQualityScores($extractions): array
    {
        return [
            'average' => $extractions->where('status', 'completed')->avg('quality_score'),
            'high' => $extractions->where('status', 'completed')->where('quality_score', '>=', 0.8)->count(),
            'medium' => $extractions->where('status', 'completed')->whereBetween('quality_score', [0.6, 0.79])->count(),
            'low' => $extractions->where('status', 'completed')->where('quality_score', '<', 0.6)->count(),
        ];
    }

    private function getFileTypeDistribution($files): array
    {
        return $files->groupBy('extension')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_size' => $group->sum('file_size'),
                ];
            })
            ->toArray();
    }

    private function getStorageGrowth($userId = null, $period = '30d'): array
    {
        $growthData = [];
        $startDate = $this->getStartDate($period);
        
        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $nextDate = $date->copy()->addDay();
            
            $query = File::whereBetween('created_at', [$date, $nextDate]);
            if ($userId) {
                $query->where('uploaded_by', $userId);
            }
            
            $growthData[$date->format('Y-m-d')] = $query->sum('file_size');
        }
        
        return $growthData;
    }

    private function getLargestFiles($userId = null, $limit = 10): array
    {
        $query = File::orderBy('file_size', 'desc')->limit($limit);
        
        if ($userId) {
            $query->where('uploaded_by', $userId);
        }
        
        return $query->get()->map(function ($file) {
            return [
                'name' => $file->name,
                'size' => $file->file_size,
                'formatted_size' => $this->formatBytes($file->file_size),
                'uploaded_at' => $file->created_at->format('M j, Y'),
            ];
        })->toArray();
    }

    private function getStorageByUserType($userId = null): array
    {
        $query = File::select('user_type', DB::raw('sum(file_size) as total_size'), DB::raw('count(*) as file_count'))
            ->groupBy('user_type');
            
        if ($userId) {
            $query->where('uploaded_by', $userId);
        }
        
        return $query->get()->map(function ($item) {
            return [
                'user_type' => $item->user_type,
                'total_size' => $item->total_size,
                'formatted_size' => $this->formatBytes($item->total_size),
                'file_count' => $item->file_count,
            ];
        })->toArray();
    }

    private function getActivityByType($activities): array
    {
        return $activities->groupBy('activity_type')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();
    }

    private function getPeakActivityHours($activities): array
    {
        $hourlyData = array_fill(0, 24, 0);
        
        foreach ($activities as $activity) {
            $hour = (int) $activity->created_at->format('G');
            $hourlyData[$hour]++;
        }
        
        return $hourlyData;
    }

    private function getActivityTrends($activities): array
    {
        $trends = [];
        $startDate = now()->subDays(7);
        
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $trends[$date] = $activities->filter(function ($activity) use ($date) {
                return $activity->created_at->format('Y-m-d') === $date;
            })->count();
        }
        
        return $trends;
    }

    private function getUserProductivity($userId = null, $period = '30d'): array
    {
        $startDate = $this->getStartDate($period);
        
        $query = RecentActivity::where('created_at', '>=', $startDate)
            ->select('user_id', 'activity_type', DB::raw('count(*) as count'))
            ->groupBy('user_id', 'activity_type');
            
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $activities = $query->get();
        
        return [
            'uploads_per_user' => $activities->where('activity_type', 'upload')->avg('count'),
            'downloads_per_user' => $activities->where('activity_type', 'download')->avg('count'),
            'views_per_user' => $activities->where('activity_type', 'view')->avg('count'),
        ];
    }

    private function getCollaborationMetrics($userId = null, $period = '30d'): array
    {
        $startDate = $this->getStartDate($period);
        
        $query = RecentActivity::where('created_at', '>=', $startDate)
            ->whereIn('activity_type', ['comment_add', 'annotation_add', 'share']);
            
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $activities = $query->get();
        
        return [
            'total_collaborations' => $activities->count(),
            'comments' => $activities->where('activity_type', 'comment_add')->count(),
            'annotations' => $activities->where('activity_type', 'annotation_add')->count(),
            'shares' => $activities->where('activity_type', 'share')->count(),
        ];
    }

    private function getActivityDescription($activity): string
    {
        return match ($activity->activity_type) {
            'upload' => 'Uploaded a file',
            'download' => 'Downloaded a file',
            'view' => 'Viewed a file',
            'share' => 'Shared a file',
            'comment_add' => 'Added a comment',
            'annotation_add' => 'Added an annotation',
            default => 'Performed an action',
        };
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function getStorageAvailable(): string
    {
        $totalSpace = disk_total_space(storage_path());
        $freeSpace = disk_free_space(storage_path());
        $usedSpace = $totalSpace - $freeSpace;
        
        return $this->formatBytes($freeSpace) . ' / ' . $this->formatBytes($totalSpace);
    }

    /**
     * Clear analytics cache
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
} 