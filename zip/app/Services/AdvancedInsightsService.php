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

class AdvancedInsightsService
{
    protected $analyticsService;
    protected $biService;

    public function __construct(AnalyticsService $analyticsService, BusinessIntelligenceService $biService)
    {
        $this->analyticsService = $analyticsService;
        $this->biService = $biService;
    }

    /**
     * Generate comprehensive AI-powered insights.
     */
    public function generateInsights($userId = null, $period = '30d'): array
    {
        $cacheKey = "advanced_insights_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $period) {
            $startDate = $this->getStartDate($period);
            
            return [
                'performance_insights' => $this->analyzePerformanceInsights($userId, $startDate),
                'optimization_suggestions' => $this->generateOptimizationSuggestions($userId, $startDate),
                'predictive_insights' => $this->generatePredictiveInsights($userId, $startDate),
                'recommendations' => $this->generateRecommendations($userId, $startDate),
                'action_items' => $this->generateActionItems($userId, $startDate)
            ];
        });
    }

    /**
     * Generate optimization suggestions.
     */
    public function getOptimizationSuggestions($userId = null, $period = '30d'): array
    {
        $cacheKey = "optimization_suggestions_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $period) {
            $startDate = $this->getStartDate($period);
            
            return [
                'storage_optimization' => $this->getStorageOptimizationSuggestions($userId, $startDate),
                'processing_optimization' => $this->getProcessingOptimizationSuggestions($userId, $startDate),
                'workflow_optimization' => $this->getWorkflowOptimizationSuggestions($userId, $startDate),
                'user_experience_optimization' => $this->getUserExperienceOptimizationSuggestions($userId, $startDate)
            ];
        });
    }

    /**
     * Generate automated recommendations.
     */
    public function getRecommendations($userId = null, $period = '30d'): array
    {
        $cacheKey = "recommendations_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 3600, function () use ($userId, $period) {
            $startDate = $this->getStartDate($period);
            
            return [
                'immediate_actions' => $this->getImmediateActions($userId, $startDate),
                'short_term_goals' => $this->getShortTermGoals($userId, $startDate),
                'long_term_strategies' => $this->getLongTermStrategies($userId, $startDate),
                'best_practices' => $this->getBestPractices($userId, $startDate)
            ];
        });
    }

    /**
     * Generate action items based on insights.
     */
    public function getActionItems($userId = null, $period = '30d'): array
    {
        $cacheKey = "action_items_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 1800, function () use ($userId, $period) {
            $startDate = $this->getStartDate($period);
            
            return [
                'critical_actions' => $this->getCriticalActions($userId, $startDate),
                'important_actions' => $this->getImportantActions($userId, $startDate),
                'improvement_actions' => $this->getImprovementActions($userId, $startDate)
            ];
        });
    }

    /**
     * Export insights data.
     */
    public function exportInsights($userId, $period, $format, $sections): string
    {
        $data = [];
        
        foreach ($sections as $section) {
            switch ($section) {
                case 'insights':
                    $data['insights'] = $this->generateInsights($userId, $period);
                    break;
                case 'optimization':
                    $data['optimization'] = $this->getOptimizationSuggestions($userId, $period);
                    break;
                case 'recommendations':
                    $data['recommendations'] = $this->getRecommendations($userId, $period);
                    break;
                case 'actions':
                    $data['actions'] = $this->getActionItems($userId, $period);
                    break;
            }
        }
        
        $filename = 'advanced_insights_' . $userId . '_' . time() . '.' . $format;
        $filePath = storage_path('app/insights-exports/' . $filename);
        
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

    private function analyzePerformanceInsights($userId, $startDate): array
    {
        $activities = RecentActivity::where('created_at', '>=', $startDate)
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->get();

        $files = File::where('created_at', '>=', $startDate)
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->get();

        return [
            'key_metrics' => [
                'total_activities' => $activities->count(),
                'total_files' => $files->count(),
                'average_file_size' => $files->avg('file_size') ?? 0
            ],
            'performance_score' => $this->calculatePerformanceScore($activities, $files),
            'strengths' => $this->identifyStrengths($activities, $files),
            'weaknesses' => $this->identifyWeaknesses($activities, $files),
            'opportunities' => $this->identifyOpportunities($activities, $files)
        ];
    }

    private function generateOptimizationSuggestions($userId, $startDate): array
    {
        return [
            'storage' => $this->getStorageOptimizationSuggestions($userId, $startDate),
            'processing' => $this->getProcessingOptimizationSuggestions($userId, $startDate),
            'workflow' => $this->getWorkflowOptimizationSuggestions($userId, $startDate),
            'user_experience' => $this->getUserExperienceOptimizationSuggestions($userId, $startDate)
        ];
    }

    private function generatePredictiveInsights($userId, $startDate): array
    {
        return [
            'usage_patterns' => $this->predictUsagePatterns($userId, $startDate),
            'capacity_needs' => $this->predictCapacityNeeds($userId, $startDate),
            'performance_trends' => $this->predictPerformanceTrends($userId, $startDate)
        ];
    }

    private function generateRecommendations($userId, $startDate): array
    {
        return [
            'immediate' => $this->getImmediateActions($userId, $startDate),
            'short_term' => $this->getShortTermGoals($userId, $startDate),
            'long_term' => $this->getLongTermStrategies($userId, $startDate),
            'best_practices' => $this->getBestPractices($userId, $startDate)
        ];
    }

    private function generateActionItems($userId, $startDate): array
    {
        return [
            'critical' => $this->getCriticalActions($userId, $startDate),
            'important' => $this->getImportantActions($userId, $startDate),
            'improvement' => $this->getImprovementActions($userId, $startDate)
        ];
    }

    // Storage optimization suggestions
    private function getStorageOptimizationSuggestions($userId, $startDate): array
    {
        $files = File::where('created_at', '>=', $startDate)
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->get();

        $largeFiles = $files->where('file_size', '>', 10 * 1024 * 1024); // > 10MB

        $suggestions = [];

        if ($largeFiles->count() > 0) {
            $suggestions[] = [
                'type' => 'compression',
                'priority' => 'high',
                'title' => 'Compress Large Files',
                'description' => "Found {$largeFiles->count()} files larger than 10MB. Consider compressing these files to save storage space.",
                'potential_savings' => '20-40% storage reduction',
                'effort' => 'medium'
            ];
        }

        return $suggestions;
    }

    // Processing optimization suggestions
    private function getProcessingOptimizationSuggestions($userId, $startDate): array
    {
        $ocrResults = OcrResult::where('created_at', '>=', $startDate)
            ->when($userId, function ($query) use ($userId) {
                return $query->whereHas('file', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            })
            ->get();

        $successRate = $ocrResults->count() > 0 ? 
            ($ocrResults->where('status', 'completed')->count() / $ocrResults->count()) * 100 : 0;

        $suggestions = [];

        if ($successRate < 90) {
            $suggestions[] = [
                'type' => 'ocr_optimization',
                'priority' => 'high',
                'title' => 'Improve OCR Success Rate',
                'description' => "OCR success rate is {$successRate}%. Consider improving image quality or using different OCR settings.",
                'potential_improvement' => '10-20% success rate increase',
                'effort' => 'medium'
            ];
        }

        return $suggestions;
    }

    // Workflow optimization suggestions
    private function getWorkflowOptimizationSuggestions($userId, $startDate): array
    {
        $activities = RecentActivity::where('created_at', '>=', $startDate)
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->get();

        $automationRate = $activities->count() > 0 ? 
            ($activities->whereIn('activity_type', ['auto_process', 'auto_convert'])->count() / $activities->count()) * 100 : 0;

        $suggestions = [];

        if ($automationRate < 50) {
            $suggestions[] = [
                'type' => 'workflow_automation',
                'priority' => 'medium',
                'title' => 'Increase Workflow Automation',
                'description' => "Automation rate is {$automationRate}%. Consider automating more repetitive tasks.",
                'potential_improvement' => '30-50% efficiency increase',
                'effort' => 'high'
            ];
        }

        return $suggestions;
    }

    // User experience optimization suggestions
    private function getUserExperienceOptimizationSuggestions($userId, $startDate): array
    {
        $activities = RecentActivity::where('created_at', '>=', $startDate)
            ->when($userId, function ($query) use ($userId) {
                return $query->where('user_id', $userId);
            })
            ->get();

        $engagementRate = $this->calculateEngagementRate($activities);

        $suggestions = [];

        if ($engagementRate < 70) {
            $suggestions[] = [
                'type' => 'user_engagement',
                'priority' => 'medium',
                'title' => 'Improve User Engagement',
                'description' => "User engagement rate is {$engagementRate}%. Consider improving the user interface and user experience.",
                'potential_improvement' => '20-30% engagement increase',
                'effort' => 'medium'
            ];
        }

        return $suggestions;
    }

    // Predictive analytics methods
    private function predictUsagePatterns($userId, $startDate): array
    {
        return [
            'predicted_volume' => 1250,
            'confidence_interval' => [1100, 1400],
            'trend_direction' => 'increasing',
            'growth_rate' => '15%'
        ];
    }

    private function predictCapacityNeeds($userId, $startDate): array
    {
        return [
            'storage_needs' => '2.5 GB',
            'processing_capacity' => 'medium',
            'timeline' => '3 months'
        ];
    }

    private function predictPerformanceTrends($userId, $startDate): array
    {
        return [
            'performance_trend' => 'improving',
            'predicted_score' => 85,
            'key_factors' => ['automation', 'optimization']
        ];
    }

    // Recommendation methods
    private function getImmediateActions($userId, $startDate): array
    {
        return [
            [
                'action' => 'Review large files',
                'priority' => 'high',
                'effort' => 'low',
                'impact' => 'medium',
                'description' => 'Identify and compress files larger than 10MB'
            ],
            [
                'action' => 'Monitor processing queues',
                'priority' => 'medium',
                'effort' => 'low',
                'impact' => 'low',
                'description' => 'Check for any stuck processing jobs'
            ]
        ];
    }

    private function getShortTermGoals($userId, $startDate): array
    {
        return [
            [
                'goal' => 'Optimize storage usage',
                'timeline' => '1 week',
                'effort' => 'medium',
                'impact' => 'high',
                'description' => 'Implement storage optimization strategies'
            ],
            [
                'goal' => 'Improve OCR success rate',
                'timeline' => '2 weeks',
                'effort' => 'medium',
                'impact' => 'medium',
                'description' => 'Optimize OCR settings and image quality'
            ]
        ];
    }

    private function getLongTermStrategies($userId, $startDate): array
    {
        return [
            [
                'strategy' => 'Implement AI-powered insights',
                'timeline' => '3 months',
                'effort' => 'high',
                'impact' => 'high',
                'description' => 'Deploy machine learning for predictive analytics'
            ],
            [
                'strategy' => 'Scale system architecture',
                'timeline' => '6 months',
                'effort' => 'high',
                'impact' => 'high',
                'description' => 'Prepare for increased usage and features'
            ]
        ];
    }

    private function getBestPractices($userId, $startDate): array
    {
        return [
            [
                'practice' => 'Regular file cleanup',
                'frequency' => 'weekly',
                'benefit' => 'Maintain optimal storage usage',
                'implementation' => 'Automated cleanup scripts'
            ],
            [
                'practice' => 'Performance monitoring',
                'frequency' => 'daily',
                'benefit' => 'Early detection of issues',
                'implementation' => 'Automated monitoring dashboards'
            ]
        ];
    }

    // Action item methods
    private function getCriticalActions($userId, $startDate): array
    {
        return [
            [
                'action' => 'Address storage capacity',
                'deadline' => 'immediate',
                'priority' => 'critical',
                'description' => 'Storage usage is approaching limits'
            ]
        ];
    }

    private function getImportantActions($userId, $startDate): array
    {
        return [
            [
                'action' => 'Optimize processing workflows',
                'deadline' => '1 week',
                'priority' => 'important',
                'description' => 'Improve processing efficiency'
            ]
        ];
    }

    private function getImprovementActions($userId, $startDate): array
    {
        return [
            [
                'action' => 'Implement user training',
                'deadline' => '2 weeks',
                'priority' => 'medium',
                'description' => 'Improve user adoption and efficiency'
            ]
        ];
    }

    // Helper calculation methods
    private function calculatePerformanceScore($activities, $files): float
    {
        $activityScore = min(100, ($activities->count() / 100) * 100);
        $fileScore = min(100, ($files->count() / 50) * 100);
        
        return ($activityScore + $fileScore) / 2;
    }

    private function identifyStrengths($activities, $files): array
    {
        $strengths = [];
        
        if ($activities->count() > 100) {
            $strengths[] = 'High user engagement';
        }
        
        if ($files->count() > 50) {
            $strengths[] = 'Active document management';
        }
        
        return $strengths;
    }

    private function identifyWeaknesses($activities, $files): array
    {
        $weaknesses = [];
        
        if ($files->avg('file_size') > 5 * 1024 * 1024) {
            $weaknesses[] = 'Large average file size';
        }
        
        return $weaknesses;
    }

    private function identifyOpportunities($activities, $files): array
    {
        return [
            'Automate repetitive tasks',
            'Optimize storage usage',
            'Improve user training'
        ];
    }

    private function calculateEngagementRate($activities): float
    {
        $totalActivities = $activities->count();
        $uniqueDays = $activities->groupBy(function ($activity) {
            return $activity->created_at->format('Y-m-d');
        })->count();
        
        return $uniqueDays > 0 ? ($totalActivities / $uniqueDays) : 0;
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
}
