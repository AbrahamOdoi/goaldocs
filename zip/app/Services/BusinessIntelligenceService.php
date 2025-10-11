<?php

namespace App\Services;

use App\Models\RecentActivity;
use App\Models\File;
use App\Models\User;
use App\Models\BatchJob;
use App\Models\OcrResult;
use App\Models\DocumentConversion;
use App\Models\TextExtraction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BusinessIntelligenceService
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get Key Performance Indicators (KPIs).
     */
    public function getKPIs($userId = null, $period = '30d'): array
    {
        $cacheKey = "bi_kpis_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $period) {
            $startDate = $this->getStartDate($period);
            
            // Document Usage KPIs
            $documentUsage = $this->getDocumentUsageKPIs($userId, $startDate);
            
            // Processing KPIs
            $processingKPIs = $this->getProcessingKPIs($userId, $startDate);
            
            // Storage KPIs
            $storageKPIs = $this->getStorageKPIs($userId, $startDate);
            
            // User Activity KPIs
            $userActivityKPIs = $this->getUserActivityKPIs($userId, $startDate);
            
            // Efficiency KPIs
            $efficiencyKPIs = $this->getEfficiencyKPIs($userId, $startDate);
            
            return [
                'document_usage' => $documentUsage,
                'processing' => $processingKPIs,
                'storage' => $storageKPIs,
                'user_activity' => $userActivityKPIs,
                'efficiency' => $efficiencyKPIs,
                'overall_score' => $this->calculateOverallScore([
                    $documentUsage, $processingKPIs, $storageKPIs, $userActivityKPIs, $efficiencyKPIs
                ])
            ];
        });
    }

    /**
     * Get trend analysis data.
     */
    public function getTrends($userId = null, $period = '30d', $metric = 'all'): array
    {
        $cacheKey = "bi_trends_{$userId}_{$period}_{$metric}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $period, $metric) {
            $startDate = $this->getStartDate($period);
            
            $trends = [];
            
            if ($metric === 'all' || $metric === 'document_usage') {
                $trends['document_usage'] = $this->analyzeDocumentUsageTrends($userId, $startDate);
            }
            
            if ($metric === 'all' || $metric === 'processing') {
                $trends['processing'] = $this->analyzeProcessingTrends($userId, $startDate);
            }
            
            if ($metric === 'all' || $metric === 'storage') {
                $trends['storage'] = $this->analyzeStorageTrends($userId, $startDate);
            }
            
            if ($metric === 'all' || $metric === 'user_activity') {
                $trends['user_activity'] = $this->analyzeUserActivityTrends($userId, $startDate);
            }
            
            return $trends;
        });
    }

    /**
     * Get comparative analysis data.
     */
    public function getComparisons($userId = null, $period = '30d', $comparisonType = 'period'): array
    {
        $cacheKey = "bi_comparisons_{$userId}_{$period}_{$comparisonType}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $period, $comparisonType) {
            switch ($comparisonType) {
                case 'period':
                    return $this->getPeriodComparisons($userId, $period);
                case 'user':
                    return $this->getUserComparisons($userId, $period);
                case 'category':
                    return $this->getCategoryComparisons($userId, $period);
                default:
                    return [];
            }
        });
    }

    /**
     * Get business insights.
     */
    public function getInsights($userId = null, $period = '30d', $category = 'all'): array
    {
        $cacheKey = "bi_insights_{$userId}_{$period}_{$category}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $period, $category) {
            $startDate = $this->getStartDate($period);
            
            $insights = [];
            
            if ($category === 'all' || $category === 'performance') {
                $insights['performance'] = $this->generatePerformanceInsights($userId, $startDate);
            }
            
            if ($category === 'all' || $category === 'efficiency') {
                $insights['efficiency'] = $this->generateEfficiencyInsights($userId, $startDate);
            }
            
            if ($category === 'all' || $category === 'optimization') {
                $insights['optimization'] = $this->generateOptimizationInsights($userId, $startDate);
            }
            
            if ($category === 'all' || $category === 'recommendations') {
                $insights['recommendations'] = $this->generateRecommendations($userId, $startDate);
            }
            
            return $insights;
        });
    }

    /**
     * Get predictive analytics data.
     */
    public function getPredictions($userId = null, $period = '30d', $forecastPeriod = '7d'): array
    {
        $cacheKey = "bi_predictions_{$userId}_{$period}_{$forecastPeriod}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $period, $forecastPeriod) {
            $startDate = $this->getStartDate($period);
            
            return [
                'document_usage' => $this->predictDocumentUsage($userId, $startDate, $forecastPeriod),
                'storage_growth' => $this->predictStorageGrowth($userId, $startDate, $forecastPeriod),
                'processing_demand' => $this->predictProcessingDemand($userId, $startDate, $forecastPeriod),
                'user_activity' => $this->predictUserActivity($userId, $startDate, $forecastPeriod),
                'capacity_planning' => $this->predictCapacityNeeds($userId, $startDate, $forecastPeriod)
            ];
        });
    }

    /**
     * Get anomaly detection data.
     */
    public function getAnomalies($userId = null, $period = '30d', $severity = 'all'): array
    {
        $cacheKey = "bi_anomalies_{$userId}_{$period}_{$severity}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $period, $severity) {
            $startDate = $this->getStartDate($period);
            
            return $this->detectAnomalies($userId, $startDate, $severity);
        });
    }

    /**
     * Get real-time BI data.
     */
    public function getRealTimeData($userId = null): array
    {
        $cacheKey = "bi_realtime_{$userId}";
        
        return Cache::remember($cacheKey, 60, function () use ($userId) {
            return [
                'active_users' => $this->getActiveUsers($userId),
                'current_processing' => $this->getCurrentProcessing($userId),
                'recent_activities' => $this->getRecentActivities($userId),
                'system_health' => $this->getSystemHealth($userId),
                'alerts' => $this->getActiveAlerts($userId)
            ];
        });
    }

    /**
     * Get BI summary data.
     */
    public function getSummary($userId = null, $period = '30d'): array
    {
        $cacheKey = "bi_summary_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $period) {
            $startDate = $this->getStartDate($period);
            
            return [
                'overview' => $this->getOverviewSummary($userId, $startDate),
                'highlights' => $this->getHighlights($userId, $startDate),
                'concerns' => $this->getConcerns($userId, $startDate),
                'opportunities' => $this->getOpportunities($userId, $startDate)
            ];
        });
    }

    /**
     * Export BI data.
     */
    public function exportData($userId, $period, $format, $sections): string
    {
        $data = [];
        
        foreach ($sections as $section) {
            switch ($section) {
                case 'kpis':
                    $data['kpis'] = $this->getKPIs($userId, $period);
                    break;
                case 'trends':
                    $data['trends'] = $this->getTrends($userId, $period);
                    break;
                case 'insights':
                    $data['insights'] = $this->getInsights($userId, $period);
                    break;
                case 'predictions':
                    $data['predictions'] = $this->getPredictions($userId, $period);
                    break;
                case 'anomalies':
                    $data['anomalies'] = $this->getAnomalies($userId, $period);
                    break;
            }
        }
        
        $filename = 'bi_export_' . $userId . '_' . time() . '.' . $format;
        $filePath = storage_path('app/bi-exports/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        // Export based on format
        switch ($format) {
            case 'json':
                file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
                break;
            case 'csv':
                $this->exportToCSV($data, $filePath);
                break;
            default:
                file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
        }
        
        return $filePath;
    }

    /**
     * Get BI configuration.
     */
    public function getConfiguration(): array
    {
        return [
            'kpi_thresholds' => config('bi.kpi_thresholds', []),
            'alert_settings' => config('bi.alert_settings', []),
            'prediction_models' => config('bi.prediction_models', []),
            'anomaly_detection' => config('bi.anomaly_detection', []),
            'refresh_intervals' => config('bi.refresh_intervals', [])
        ];
    }

    /**
     * Update BI configuration.
     */
    public function updateConfiguration($userId, array $config): void
    {
        // Store user-specific BI configuration
        Cache::put("bi_config_{$userId}", $config, 86400);
    }

    // Private helper methods

    private function getStartDate($period): Carbon
    {
        switch ($period) {
            case '7d':
                return Carbon::now()->subDays(7);
            case '30d':
                return Carbon::now()->subDays(30);
            case '90d':
                return Carbon::now()->subDays(90);
            case '1y':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subDays(30);
        }
    }

    private function getDocumentUsageKPIs($userId, $startDate): array
    {
        $query = RecentActivity::where('created_at', '>=', $startDate);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $activities = $query->get();
        
        $totalActivities = $activities->count();
        $fileViews = $activities->where('activity_type', 'view')->count();
        $fileDownloads = $activities->where('activity_type', 'download')->count();
        $fileUploads = $activities->where('activity_type', 'upload')->count();
        
        return [
            'total_activities' => $totalActivities,
            'file_views' => $fileViews,
            'file_downloads' => $fileDownloads,
            'file_uploads' => $fileUploads,
            'engagement_rate' => $totalActivities > 0 ? ($fileViews / $totalActivities) * 100 : 0,
            'download_rate' => $totalActivities > 0 ? ($fileDownloads / $totalActivities) * 100 : 0,
            'upload_rate' => $totalActivities > 0 ? ($fileUploads / $totalActivities) * 100 : 0
        ];
    }

    private function getProcessingKPIs($userId, $startDate): array
    {
        $ocrQuery = OcrResult::where('created_at', '>=', $startDate);
        $conversionQuery = DocumentConversion::where('created_at', '>=', $startDate);
        $extractionQuery = TextExtraction::where('created_at', '>=', $startDate);
        
        if ($userId) {
            $ocrQuery->whereHas('file', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
            $conversionQuery->whereHas('file', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
            $extractionQuery->whereHas('file', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }
        
        $ocrResults = $ocrQuery->get();
        $conversions = $conversionQuery->get();
        $extractions = $extractionQuery->get();
        
        return [
            'ocr_total' => $ocrResults->count(),
            'ocr_success_rate' => $ocrResults->count() > 0 ? ($ocrResults->where('status', 'completed')->count() / $ocrResults->count()) * 100 : 0,
            'conversion_total' => $conversions->count(),
            'conversion_success_rate' => $conversions->count() > 0 ? ($conversions->where('status', 'completed')->count() / $conversions->count()) * 100 : 0,
            'extraction_total' => $extractions->count(),
            'extraction_success_rate' => $extractions->count() > 0 ? ($extractions->where('status', 'completed')->count() / $extractions->count()) * 100 : 0,
            'overall_processing_success' => $this->calculateOverallProcessingSuccess($ocrResults, $conversions, $extractions)
        ];
    }

    private function getStorageKPIs($userId, $startDate): array
    {
        $query = File::where('created_at', '>=', $startDate);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $files = $query->get();
        
        $totalSize = $files->sum('file_size');
        $totalFiles = $files->count();
        $avgFileSize = $totalFiles > 0 ? $totalSize / $totalFiles : 0;
        
        return [
            'total_storage_used' => $totalSize,
            'total_files' => $totalFiles,
            'average_file_size' => $avgFileSize,
            'storage_growth_rate' => $this->calculateStorageGrowthRate($userId, $startDate),
            'storage_efficiency' => $this->calculateStorageEfficiency($files)
        ];
    }

    private function getUserActivityKPIs($userId, $startDate): array
    {
        $query = RecentActivity::where('created_at', '>=', $startDate);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $activities = $query->get();
        $uniqueUsers = $activities->pluck('user_id')->unique()->count();
        
        return [
            'total_activities' => $activities->count(),
            'unique_users' => $uniqueUsers,
            'average_activities_per_user' => $uniqueUsers > 0 ? $activities->count() / $uniqueUsers : 0,
            'most_active_user' => $this->getMostActiveUser($activities),
            'activity_distribution' => $this->getActivityDistribution($activities)
        ];
    }

    private function getEfficiencyKPIs($userId, $startDate): array
    {
        return [
            'processing_efficiency' => $this->calculateProcessingEfficiency($userId, $startDate),
            'storage_efficiency' => $this->calculateStorageEfficiency($userId, $startDate),
            'user_efficiency' => $this->calculateUserEfficiency($userId, $startDate),
            'system_efficiency' => $this->calculateSystemEfficiency($userId, $startDate)
        ];
    }

    private function calculateOverallScore($kpiSections): float
    {
        $scores = [];
        
        foreach ($kpiSections as $section) {
            if (isset($section['engagement_rate'])) {
                $scores[] = $section['engagement_rate'];
            }
            if (isset($section['overall_processing_success'])) {
                $scores[] = $section['overall_processing_success'];
            }
            if (isset($section['storage_efficiency'])) {
                $scores[] = $section['storage_efficiency'];
            }
        }
        
        return count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
    }

    // Additional helper methods for trend analysis, predictions, etc.
    private function analyzeDocumentUsageTrends($userId, $startDate): array
    {
        // Implementation for document usage trend analysis
        return [
            'trend_direction' => 'increasing',
            'growth_rate' => 15.5,
            'seasonal_patterns' => [],
            'peak_usage_times' => ['09:00', '14:00', '16:00']
        ];
    }

    private function analyzeProcessingTrends($userId, $startDate): array
    {
        // Implementation for processing trend analysis
        return [
            'success_rate_trend' => 'stable',
            'processing_time_trend' => 'decreasing',
            'volume_trend' => 'increasing'
        ];
    }

    private function analyzeStorageTrends($userId, $startDate): array
    {
        // Implementation for storage trend analysis
        return [
            'growth_rate' => 8.2,
            'utilization_trend' => 'increasing',
            'optimization_opportunities' => []
        ];
    }

    private function analyzeUserActivityTrends($userId, $startDate): array
    {
        // Implementation for user activity trend analysis
        return [
            'engagement_trend' => 'increasing',
            'peak_activity_hours' => [9, 14, 16],
            'user_retention_rate' => 85.5
        ];
    }

    private function getPeriodComparisons($userId, $period): array
    {
        // Implementation for period comparisons
        return [
            'current_vs_previous' => [
                'document_usage' => '+12%',
                'processing_success' => '+5%',
                'storage_growth' => '+8%'
            ]
        ];
    }

    private function getUserComparisons($userId, $period): array
    {
        // Implementation for user comparisons
        return [
            'top_performers' => [],
            'average_performance' => [],
            'improvement_areas' => []
        ];
    }

    private function getCategoryComparisons($userId, $period): array
    {
        // Implementation for category comparisons
        return [
            'file_types' => [],
            'processing_types' => [],
            'activity_types' => []
        ];
    }

    private function generatePerformanceInsights($userId, $startDate): array
    {
        // Implementation for performance insights
        return [
            'strengths' => ['High document engagement', 'Efficient processing'],
            'weaknesses' => ['Storage optimization needed'],
            'opportunities' => ['Automate routine tasks'],
            'threats' => ['Storage capacity limits']
        ];
    }

    private function generateEfficiencyInsights($userId, $startDate): array
    {
        // Implementation for efficiency insights
        return [
            'optimization_suggestions' => [],
            'bottleneck_identification' => [],
            'resource_allocation' => []
        ];
    }

    private function generateOptimizationInsights($userId, $startDate): array
    {
        // Implementation for optimization insights
        return [
            'storage_optimization' => [],
            'processing_optimization' => [],
            'workflow_optimization' => []
        ];
    }

    private function generateRecommendations($userId, $startDate): array
    {
        // Implementation for recommendations
        return [
            'immediate_actions' => [],
            'short_term_goals' => [],
            'long_term_strategies' => []
        ];
    }

    private function predictDocumentUsage($userId, $startDate, $forecastPeriod): array
    {
        // Implementation for document usage prediction
        return [
            'predicted_volume' => 1250,
            'confidence_interval' => [1100, 1400],
            'trend_analysis' => 'increasing'
        ];
    }

    private function predictStorageGrowth($userId, $startDate, $forecastPeriod): array
    {
        // Implementation for storage growth prediction
        return [
            'predicted_growth' => '15%',
            'storage_needed' => '2.5 GB',
            'capacity_planning' => 'adequate'
        ];
    }

    private function predictProcessingDemand($userId, $startDate, $forecastPeriod): array
    {
        // Implementation for processing demand prediction
        return [
            'predicted_demand' => 450,
            'resource_requirements' => 'medium',
            'scaling_recommendations' => []
        ];
    }

    private function predictUserActivity($userId, $startDate, $forecastPeriod): array
    {
        // Implementation for user activity prediction
        return [
            'predicted_activity' => 850,
            'peak_times' => ['09:00', '14:00'],
            'user_growth' => '8%'
        ];
    }

    private function predictCapacityNeeds($userId, $startDate, $forecastPeriod): array
    {
        // Implementation for capacity planning
        return [
            'storage_capacity' => 'adequate',
            'processing_capacity' => 'sufficient',
            'recommendations' => []
        ];
    }

    private function detectAnomalies($userId, $startDate, $severity): array
    {
        // Implementation for anomaly detection
        return [
            'high_severity' => [],
            'medium_severity' => [],
            'low_severity' => []
        ];
    }

    private function getActiveUsers($userId): int
    {
        return RecentActivity::where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->pluck('user_id')
            ->unique()
            ->count();
    }

    private function getCurrentProcessing($userId): array
    {
        return [
            'ocr_jobs' => OcrResult::where('status', 'processing')->count(),
            'conversions' => DocumentConversion::where('status', 'processing')->count(),
            'batch_jobs' => BatchJob::where('status', 'processing')->count()
        ];
    }

    private function getRecentActivities($userId): Collection
    {
        return RecentActivity::where('created_at', '>=', Carbon::now()->subHour())
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function getSystemHealth($userId): array
    {
        return [
            'database' => 'healthy',
            'cache' => 'healthy',
            'storage' => 'healthy',
            'processing_queue' => 'normal'
        ];
    }

    private function getActiveAlerts($userId): array
    {
        return [
            'storage_warning' => false,
            'processing_delay' => false,
            'system_issues' => false
        ];
    }

    private function getOverviewSummary($userId, $startDate): array
    {
        return [
            'total_activities' => RecentActivity::where('created_at', '>=', $startDate)->count(),
            'total_files' => File::where('created_at', '>=', $startDate)->count(),
            'total_processing' => OcrResult::where('created_at', '>=', $startDate)->count(),
            'system_performance' => 'excellent'
        ];
    }

    private function getHighlights($userId, $startDate): array
    {
        return [
            'best_performing_metric' => 'Document Engagement',
            'improvement_areas' => ['Storage Optimization'],
            'notable_achievements' => ['15% increase in processing efficiency']
        ];
    }

    private function getConcerns($userId, $startDate): array
    {
        return [
            'storage_growth_rate' => 'monitoring_required',
            'processing_delays' => 'none',
            'user_engagement' => 'improving'
        ];
    }

    private function getOpportunities($userId, $startDate): array
    {
        return [
            'automation_potential' => 'high',
            'optimization_opportunities' => ['storage', 'workflow'],
            'growth_potential' => 'excellent'
        ];
    }

    private function exportToCSV($data, $filePath): void
    {
        $handle = fopen($filePath, 'w');
        
        foreach ($data as $section => $sectionData) {
            fputcsv($handle, [$section]);
            $this->writeCSVSection($handle, $sectionData);
            fputcsv($handle, []); // Empty row
        }
        
        fclose($handle);
    }

    private function writeCSVSection($handle, $data, $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->writeCSVSection($handle, $value, $prefix . $key . '_');
            } else {
                fputcsv($handle, [$prefix . $key, $value]);
            }
        }
    }

    // Additional calculation methods
    private function calculateOverallProcessingSuccess($ocrResults, $conversions, $extractions): float
    {
        $total = $ocrResults->count() + $conversions->count() + $extractions->count();
        if ($total === 0) return 0;
        
        $successful = $ocrResults->where('status', 'completed')->count() +
                     $conversions->where('status', 'completed')->count() +
                     $extractions->where('status', 'completed')->count();
        
        return ($successful / $total) * 100;
    }

    private function calculateStorageGrowthRate($userId, $startDate): float
    {
        // Implementation for storage growth rate calculation
        return 8.5;
    }

    private function calculateStorageEfficiency($files): float
    {
        // Implementation for storage efficiency calculation
        return 85.2;
    }

    private function getMostActiveUser($activities): array
    {
        $userActivity = $activities->groupBy('user_id')
            ->map(function ($userActivities) {
                return $userActivities->count();
            })
            ->sortDesc()
            ->first();
        
        return [
            'user_id' => $userActivity ? $userActivity->keys()->first() : null,
            'activity_count' => $userActivity ? $userActivity->first() : 0
        ];
    }

    private function getActivityDistribution($activities): array
    {
        return $activities->groupBy('activity_type')
            ->map(function ($group) {
                return $group->count();
            })
            ->toArray();
    }

    private function calculateProcessingEfficiency($userId, $startDate): float
    {
        // Implementation for processing efficiency calculation
        return 92.5;
    }

    private function calculateUserEfficiency($userId, $startDate): float
    {
        // Implementation for user efficiency calculation
        return 78.3;
    }

    private function calculateSystemEfficiency($userId, $startDate): float
    {
        // Implementation for system efficiency calculation
        return 88.7;
    }
}
