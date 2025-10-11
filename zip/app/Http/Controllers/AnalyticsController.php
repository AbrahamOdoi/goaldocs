<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show analytics dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $analytics = $this->analyticsService->getDashboardAnalytics($user->id);
        
        return view('analytics.dashboard', compact('analytics'));
    }

    /**
     * Get document usage analytics
     */
    public function documentUsage(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $analytics = $this->analyticsService->getDocumentUsageAnalytics($user->id, $period);
        
        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get processing analytics
     */
    public function processing(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $analytics = $this->analyticsService->getProcessingAnalytics($user->id, $period);
        
        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get storage analytics
     */
    public function storage(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $analytics = $this->analyticsService->getStorageAnalytics($user->id, $period);
        
        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get user activity analytics
     */
    public function userActivity(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $analytics = $this->analyticsService->getUserActivityAnalytics($user->id, $period);
        
        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get comprehensive analytics data
     */
    public function getData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $section = $request->get('section', 'dashboard');
        $period = $request->get('period', '30d');
        
        $data = match ($section) {
            'dashboard' => $this->analyticsService->getDashboardAnalytics($user->id),
            'document_usage' => $this->analyticsService->getDocumentUsageAnalytics($user->id, $period),
            'processing' => $this->analyticsService->getProcessingAnalytics($user->id, $period),
            'storage' => $this->analyticsService->getStorageAnalytics($user->id, $period),
            'user_activity' => $this->analyticsService->getUserActivityAnalytics($user->id, $period),
            default => $this->analyticsService->getDashboardAnalytics($user->id),
        };
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'section' => $section,
            'period' => $period,
        ]);
    }

    /**
     * Show document usage analytics page
     */
    public function documentUsagePage()
    {
        $user = Auth::user();
        $analytics = $this->analyticsService->getDocumentUsageAnalytics($user->id, '30d');
        
        return view('analytics.document-usage', compact('analytics'));
    }

    /**
     * Show processing analytics page
     */
    public function processingPage()
    {
        $user = Auth::user();
        $analytics = $this->analyticsService->getProcessingAnalytics($user->id, '30d');
        
        return view('analytics.processing', compact('analytics'));
    }

    /**
     * Show storage analytics page
     */
    public function storagePage()
    {
        $user = Auth::user();
        $analytics = $this->analyticsService->getStorageAnalytics($user->id, '30d');
        
        return view('analytics.storage', compact('analytics'));
    }

    /**
     * Show user activity analytics page
     */
    public function userActivityPage()
    {
        $user = Auth::user();
        $analytics = $this->analyticsService->getUserActivityAnalytics($user->id, '30d');
        
        return view('analytics.user-activity', compact('analytics'));
    }

    /**
     * Generate analytics report
     */
    public function generateReport(Request $request): JsonResponse
    {
        $user = Auth::user();
        $format = $request->get('format', 'json');
        $sections = $request->get('sections', ['dashboard']);
        $period = $request->get('period', '30d');
        
        $report = [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => $user->name,
            'period' => $period,
            'sections' => [],
        ];
        
        foreach ($sections as $section) {
            switch ($section) {
                case 'document_usage':
                    $report['sections'][$section] = $this->analyticsService->getDocumentUsageAnalytics($user->id, $period);
                    break;
                case 'processing':
                    $report['sections'][$section] = $this->analyticsService->getProcessingAnalytics($user->id, $period);
                    break;
                case 'storage':
                    $report['sections'][$section] = $this->analyticsService->getStorageAnalytics($user->id, $period);
                    break;
                case 'user_activity':
                    $report['sections'][$section] = $this->analyticsService->getUserActivityAnalytics($user->id, $period);
                    break;
                case 'dashboard':
                    $report['sections'][$section] = $this->analyticsService->getDashboardAnalytics($user->id);
                    break;
            }
        }
        
        if ($format === 'pdf') {
            // In a real implementation, you would generate a PDF report
            return response()->json([
                'success' => true,
                'message' => 'PDF report generation would be implemented here',
                'report' => $report,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'report' => $report,
        ]);
    }

    /**
     * Export analytics data
     */
    public function export(Request $request): JsonResponse
    {
        $user = Auth::user();
        $format = $request->get('format', 'csv');
        $section = $request->get('section', 'dashboard');
        $period = $request->get('period', '30d');
        
        $data = match ($section) {
            'document_usage' => $this->analyticsService->getDocumentUsageAnalytics($user->id, $period),
            'processing' => $this->analyticsService->getProcessingAnalytics($user->id, $period),
            'storage' => $this->analyticsService->getStorageAnalytics($user->id, $period),
            'user_activity' => $this->analyticsService->getUserActivityAnalytics($user->id, $period),
            'dashboard' => $this->analyticsService->getDashboardAnalytics($user->id),
            default => $this->analyticsService->getDashboardAnalytics($user->id),
        };
        
        if ($format === 'csv') {
            // In a real implementation, you would generate a CSV file
            return response()->json([
                'success' => true,
                'message' => 'CSV export would be implemented here',
                'data' => $data,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Clear analytics cache
     */
    public function clearCache(): JsonResponse
    {
        $user = Auth::user();
        $this->analyticsService->clearCache();
        
        return response()->json([
            'success' => true,
            'message' => 'Analytics cache cleared successfully',
        ]);
    }

    /**
     * Get real-time analytics
     */
    public function realTime(): JsonResponse
    {
        $user = Auth::user();
        
        // Get real-time data (last 24 hours)
        $recentFiles = \App\Models\File::where('uploaded_by', $user->id)
            ->where('created_at', '>', now()->subDay())
            ->count();
        
        $recentActivities = \App\Models\RecentActivity::where('user_id', $user->id)
            ->where('created_at', '>', now()->subDay())
            ->count();
        
        $batchJobs = \App\Models\BatchJob::where('user_id', $user->id)
            ->where('created_at', '>', now()->subDay())
            ->count();
        
        $storageUsed = \App\Models\File::where('uploaded_by', $user->id)
            ->where('created_at', '>', now()->subDay())
            ->sum('file_size');
        
        return response()->json([
            'success' => true,
            'data' => [
                'recent_files' => $recentFiles,
                'recent_activities' => $recentActivities,
                'batch_jobs' => $batchJobs,
                'storage_used' => $storageUsed,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Get analytics summary for dashboard widgets
     */
    public function summary(): JsonResponse
    {
        $user = Auth::user();
        $analytics = $this->analyticsService->getDashboardAnalytics($user->id);
        
        $summary = [
            'quick_stats' => $analytics['quick_stats'],
            'system_health' => $analytics['system_health'],
            'recent_activity' => array_slice($analytics['recent_activity'], 0, 5),
        ];
        
        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get analytics trends
     */
    public function trends(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $documentUsage = $this->analyticsService->getDocumentUsageAnalytics($user->id, $period);
        $storage = $this->analyticsService->getStorageAnalytics($user->id, $period);
        
        $trends = [
            'daily_activity' => $documentUsage['daily_activity'],
            'storage_growth' => $storage['storage_growth'],
            'activity_trends' => $this->analyticsService->getUserActivityAnalytics($user->id, $period)['activity_trends'],
        ];
        
        return response()->json([
            'success' => true,
            'data' => $trends,
        ]);
    }
} 