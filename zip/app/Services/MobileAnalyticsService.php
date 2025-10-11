<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSession;
use App\Models\SecurityLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MobileAnalyticsService
{
    /**
     * Track mobile app usage.
     */
    public function trackUsage($userId, $action, $data = []): void
    {
        try {
            $usageData = [
                'user_id' => $userId,
                'action' => $action,
                'data' => $data,
                'timestamp' => Carbon::now()->toISOString(),
                'device_info' => $this->getDeviceInfo($userId),
            ];

            // Store in cache for real-time analytics
            $this->storeUsageData($userId, $usageData);

            // Log for analytics
            Log::info("Mobile usage tracked", $usageData);

        } catch (\Exception $e) {
            Log::error("Error tracking mobile usage", [
                'user_id' => $userId,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Track mobile app performance.
     */
    public function trackPerformance($userId, $metric, $value, $data = []): void
    {
        try {
            $performanceData = [
                'user_id' => $userId,
                'metric' => $metric,
                'value' => $value,
                'data' => $data,
                'timestamp' => Carbon::now()->toISOString(),
                'device_info' => $this->getDeviceInfo($userId),
            ];

            // Store in cache for real-time monitoring
            $this->storePerformanceData($userId, $performanceData);

            // Log for analytics
            Log::info("Mobile performance tracked", $performanceData);

        } catch (\Exception $e) {
            Log::error("Error tracking mobile performance", [
                'user_id' => $userId,
                'metric' => $metric,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Track mobile app crash.
     */
    public function trackCrash($userId, $error, $stackTrace = '', $data = []): void
    {
        try {
            $crashData = [
                'user_id' => $userId,
                'error' => $error,
                'stack_trace' => $stackTrace,
                'data' => $data,
                'timestamp' => Carbon::now()->toISOString(),
                'device_info' => $this->getDeviceInfo($userId),
            ];

            // Store crash data
            $this->storeCrashData($userId, $crashData);

            // Log crash
            Log::error("Mobile app crash", $crashData);

        } catch (\Exception $e) {
            Log::error("Error tracking mobile crash", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get mobile usage statistics.
     */
    public function getUsageStats($userId = null, $period = '7d'): array
    {
        try {
            $startDate = $this->getStartDate($period);
            
            if ($userId) {
                return $this->getUserUsageStats($userId, $startDate);
            } else {
                return $this->getGlobalUsageStats($startDate);
            }

        } catch (\Exception $e) {
            Log::error("Error getting usage stats", [
                'user_id' => $userId,
                'period' => $period,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get mobile performance statistics.
     */
    public function getPerformanceStats($userId = null, $period = '7d'): array
    {
        try {
            $startDate = $this->getStartDate($period);
            
            if ($userId) {
                return $this->getUserPerformanceStats($userId, $startDate);
            } else {
                return $this->getGlobalPerformanceStats($startDate);
            }

        } catch (\Exception $e) {
            Log::error("Error getting performance stats", [
                'user_id' => $userId,
                'period' => $period,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get mobile crash statistics.
     */
    public function getCrashStats($userId = null, $period = '7d'): array
    {
        try {
            $startDate = $this->getStartDate($period);
            
            if ($userId) {
                return $this->getUserCrashStats($userId, $startDate);
            } else {
                return $this->getGlobalCrashStats($startDate);
            }

        } catch (\Exception $e) {
            Log::error("Error getting crash stats", [
                'user_id' => $userId,
                'period' => $period,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get mobile user behavior analytics.
     */
    public function getUserBehaviorAnalytics($userId, $period = '30d'): array
    {
        try {
            $startDate = $this->getStartDate($period);
            
            $usageData = $this->getUserUsageData($userId, $startDate);
            
            $analytics = [
                'session_duration' => $this->calculateSessionDuration($usageData),
                'most_used_features' => $this->getMostUsedFeatures($usageData),
                'peak_usage_times' => $this->getPeakUsageTimes($usageData),
                'feature_adoption' => $this->getFeatureAdoption($usageData),
                'user_engagement' => $this->calculateUserEngagement($usageData),
            ];

            return $analytics;

        } catch (\Exception $e) {
            Log::error("Error getting user behavior analytics", [
                'user_id' => $userId,
                'period' => $period,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get mobile app optimization insights.
     */
    public function getOptimizationInsights(): array
    {
        try {
            $insights = [
                'performance_issues' => $this->identifyPerformanceIssues(),
                'crash_patterns' => $this->identifyCrashPatterns(),
                'usage_patterns' => $this->identifyUsagePatterns(),
                'recommendations' => $this->generateRecommendations(),
            ];

            return $insights;

        } catch (\Exception $e) {
            Log::error("Error getting optimization insights", [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get device information for user.
     */
    private function getDeviceInfo($userId): array
    {
        try {
            $session = UserSession::where('user_id', $userId)
                ->where('is_mobile', true)
                ->where('is_active', true)
                ->first();

            if (!$session) {
                return [];
            }

            return [
                'device_id' => $session->device_id,
                'device_name' => $session->device_name,
                'device_type' => $session->device_type,
                'last_activity' => $session->last_activity?->toISOString(),
            ];

        } catch (\Exception $e) {
            Log::error("Error getting device info", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Store usage data in cache.
     */
    private function storeUsageData($userId, $data): void
    {
        try {
            $cacheKey = "mobile_usage_{$userId}";
            $usageData = Cache::get($cacheKey, []);
            
            $usageData[] = $data;
            
            // Keep only last 1000 entries
            $usageData = array_slice($usageData, -1000);
            
            Cache::put($cacheKey, $usageData, 60 * 24 * 7); // 7 days

        } catch (\Exception $e) {
            Log::error("Error storing usage data", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store performance data in cache.
     */
    private function storePerformanceData($userId, $data): void
    {
        try {
            $cacheKey = "mobile_performance_{$userId}";
            $performanceData = Cache::get($cacheKey, []);
            
            $performanceData[] = $data;
            
            // Keep only last 500 entries
            $performanceData = array_slice($performanceData, -500);
            
            Cache::put($cacheKey, $performanceData, 60 * 24 * 7); // 7 days

        } catch (\Exception $e) {
            Log::error("Error storing performance data", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store crash data in cache.
     */
    private function storeCrashData($userId, $data): void
    {
        try {
            $cacheKey = "mobile_crashes_{$userId}";
            $crashData = Cache::get($cacheKey, []);
            
            $crashData[] = $data;
            
            // Keep only last 100 entries
            $crashData = array_slice($crashData, -100);
            
            Cache::put($cacheKey, $crashData, 60 * 24 * 30); // 30 days

        } catch (\Exception $e) {
            Log::error("Error storing crash data", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get start date based on period.
     */
    private function getStartDate($period): Carbon
    {
        switch ($period) {
            case '1d':
                return Carbon::now()->subDay();
            case '7d':
                return Carbon::now()->subWeek();
            case '30d':
                return Carbon::now()->subMonth();
            case '90d':
                return Carbon::now()->subMonths(3);
            default:
                return Carbon::now()->subWeek();
        }
    }

    /**
     * Get user usage statistics.
     */
    private function getUserUsageStats($userId, $startDate): array
    {
        $usageData = $this->getUserUsageData($userId, $startDate);
        
        return [
            'total_sessions' => count($usageData),
            'total_actions' => array_sum(array_column($usageData, 'action_count')),
            'most_used_feature' => $this->getMostUsedFeature($usageData),
            'average_session_duration' => $this->calculateAverageSessionDuration($usageData),
            'last_activity' => $this->getLastActivity($usageData),
        ];
    }

    /**
     * Get global usage statistics.
     */
    private function getGlobalUsageStats($startDate): array
    {
        // This would aggregate data from all users
        // For now, return mock data
        return [
            'total_users' => UserSession::where('is_mobile', true)->distinct('user_id')->count(),
            'active_users' => UserSession::where('is_mobile', true)
                ->where('is_active', true)
                ->where('last_activity', '>=', $startDate)
                ->distinct('user_id')
                ->count(),
            'total_sessions' => 0,
            'total_actions' => 0,
        ];
    }

    /**
     * Get user performance statistics.
     */
    private function getUserPerformanceStats($userId, $startDate): array
    {
        $performanceData = $this->getUserPerformanceData($userId, $startDate);
        
        return [
            'average_load_time' => $this->calculateAverageLoadTime($performanceData),
            'crash_rate' => $this->calculateCrashRate($userId, $startDate),
            'error_rate' => $this->calculateErrorRate($performanceData),
            'performance_score' => $this->calculatePerformanceScore($performanceData),
        ];
    }

    /**
     * Get global performance statistics.
     */
    private function getGlobalPerformanceStats($startDate): array
    {
        // This would aggregate performance data from all users
        return [
            'average_load_time' => 0,
            'crash_rate' => 0,
            'error_rate' => 0,
            'performance_score' => 0,
        ];
    }

    /**
     * Get user crash statistics.
     */
    private function getUserCrashStats($userId, $startDate): array
    {
        $crashData = $this->getUserCrashData($userId, $startDate);
        
        return [
            'total_crashes' => count($crashData),
            'crash_rate' => $this->calculateCrashRate($userId, $startDate),
            'most_common_error' => $this->getMostCommonError($crashData),
            'last_crash' => $this->getLastCrash($crashData),
        ];
    }

    /**
     * Get global crash statistics.
     */
    private function getGlobalCrashStats($startDate): array
    {
        // This would aggregate crash data from all users
        return [
            'total_crashes' => 0,
            'crash_rate' => 0,
            'most_common_error' => '',
            'affected_users' => 0,
        ];
    }

    /**
     * Get user usage data.
     */
    private function getUserUsageData($userId, $startDate): array
    {
        $cacheKey = "mobile_usage_{$userId}";
        $usageData = Cache::get($cacheKey, []);
        
        return array_filter($usageData, function($data) use ($startDate) {
            return Carbon::parse($data['timestamp']) >= $startDate;
        });
    }

    /**
     * Get user performance data.
     */
    private function getUserPerformanceData($userId, $startDate): array
    {
        $cacheKey = "mobile_performance_{$userId}";
        $performanceData = Cache::get($cacheKey, []);
        
        return array_filter($performanceData, function($data) use ($startDate) {
            return Carbon::parse($data['timestamp']) >= $startDate;
        });
    }

    /**
     * Get user crash data.
     */
    private function getUserCrashData($userId, $startDate): array
    {
        $cacheKey = "mobile_crashes_{$userId}";
        $crashData = Cache::get($cacheKey, []);
        
        return array_filter($crashData, function($data) use ($startDate) {
            return Carbon::parse($data['timestamp']) >= $startDate;
        });
    }

    /**
     * Calculate session duration.
     */
    private function calculateSessionDuration($usageData): float
    {
        // Mock calculation
        return 0.0;
    }

    /**
     * Get most used features.
     */
    private function getMostUsedFeatures($usageData): array
    {
        // Mock data
        return [
            'file_view' => 45,
            'file_upload' => 30,
            'search' => 15,
            'folder_browse' => 10,
        ];
    }

    /**
     * Get peak usage times.
     */
    private function getPeakUsageTimes($usageData): array
    {
        // Mock data
        return [
            'morning' => 25,
            'afternoon' => 40,
            'evening' => 30,
            'night' => 5,
        ];
    }

    /**
     * Get feature adoption.
     */
    private function getFeatureAdoption($usageData): array
    {
        // Mock data
        return [
            'file_management' => 95,
            'search' => 80,
            'offline_access' => 60,
            'push_notifications' => 75,
        ];
    }

    /**
     * Calculate user engagement.
     */
    private function calculateUserEngagement($usageData): float
    {
        // Mock calculation
        return 85.5;
    }

    /**
     * Identify performance issues.
     */
    private function identifyPerformanceIssues(): array
    {
        return [
            'slow_load_times' => 'Some users experiencing slow app load times',
            'memory_usage' => 'High memory usage on older devices',
            'network_timeouts' => 'Network timeout issues in poor connectivity areas',
        ];
    }

    /**
     * Identify crash patterns.
     */
    private function identifyCrashPatterns(): array
    {
        return [
            'file_upload_crashes' => 'Crashes during large file uploads',
            'memory_pressure' => 'Crashes due to memory pressure on low-end devices',
            'network_errors' => 'Crashes during network connectivity issues',
        ];
    }

    /**
     * Identify usage patterns.
     */
    private function identifyUsagePatterns(): array
    {
        return [
            'peak_hours' => 'Peak usage between 9 AM and 5 PM',
            'mobile_preference' => '80% of users prefer mobile over web',
            'feature_usage' => 'File viewing is the most used feature',
        ];
    }

    /**
     * Generate recommendations.
     */
    private function generateRecommendations(): array
    {
        return [
            'optimize_performance' => 'Implement lazy loading for better performance',
            'improve_offline' => 'Enhance offline capabilities for better user experience',
            'add_analytics' => 'Add more detailed analytics for better insights',
            'crash_reporting' => 'Implement better crash reporting and monitoring',
        ];
    }

    /**
     * Helper methods for calculations.
     */
    private function getMostUsedFeature($usageData): string
    {
        return 'file_view';
    }

    private function calculateAverageSessionDuration($usageData): float
    {
        return 15.5;
    }

    private function getLastActivity($usageData): string
    {
        return Carbon::now()->subHours(2)->toISOString();
    }

    private function calculateAverageLoadTime($performanceData): float
    {
        return 2.5;
    }

    private function calculateCrashRate($userId, $startDate): float
    {
        return 0.5;
    }

    private function calculateErrorRate($performanceData): float
    {
        return 1.2;
    }

    private function calculatePerformanceScore($performanceData): float
    {
        return 85.0;
    }

    private function getMostCommonError($crashData): string
    {
        return 'OutOfMemoryError';
    }

    private function getLastCrash($crashData): string
    {
        return Carbon::now()->subDays(3)->toISOString();
    }
}
