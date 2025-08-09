<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
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
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        
        return view('analytics.dashboard', compact('analytics'));
    }

    /**
     * Get analytics data as JSON
     */
    public function getData(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $section = $request->get('section', 'overview');
        $analytics = $this->analyticsService->getDashboardAnalytics();
        
        $data = match ($section) {
            'overview' => $analytics['overview'],
            'files' => $analytics['file_analytics'],
            'users' => $analytics['user_analytics'],
            'search' => $analytics['search_analytics'],
            'collaboration' => $analytics['collaboration_analytics'],
            'workflows' => $analytics['workflow_analytics'],
            'security' => $analytics['security_analytics'],
            'performance' => $analytics['performance_analytics'],
            'trends' => $analytics['trends'],
            'top_performers' => $analytics['top_performers'],
            default => $analytics,
        };
        
        return response()->json([
            'success' => true,
            'data' => $data,
            'section' => $section,
        ]);
    }

    /**
     * Show file analytics
     */
    public function fileAnalytics()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        $fileAnalytics = $analytics['file_analytics'];
        
        return view('analytics.files', compact('fileAnalytics'));
    }

    /**
     * Show user analytics
     */
    public function userAnalytics()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        $userAnalytics = $analytics['user_analytics'];
        
        return view('analytics.users', compact('userAnalytics'));
    }

    /**
     * Show search analytics
     */
    public function searchAnalytics()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        $searchAnalytics = $analytics['search_analytics'];
        
        return view('analytics.search', compact('searchAnalytics'));
    }

    /**
     * Show collaboration analytics
     */
    public function collaborationAnalytics()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        $collaborationAnalytics = $analytics['collaboration_analytics'];
        
        return view('analytics.collaboration', compact('collaborationAnalytics'));
    }

    /**
     * Show workflow analytics
     */
    public function workflowAnalytics()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        $workflowAnalytics = $analytics['workflow_analytics'];
        
        return view('analytics.workflows', compact('workflowAnalytics'));
    }

    /**
     * Show security analytics
     */
    public function securityAnalytics()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        $securityAnalytics = $analytics['security_analytics'];
        
        return view('analytics.security', compact('securityAnalytics'));
    }

    /**
     * Show performance analytics
     */
    public function performanceAnalytics()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        $performanceAnalytics = $analytics['performance_analytics'];
        
        return view('analytics.performance', compact('performanceAnalytics'));
    }

    /**
     * Show trends
     */
    public function trends()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        $trends = $analytics['trends'];
        
        return view('analytics.trends', compact('trends'));
    }

    /**
     * Show top performers
     */
    public function topPerformers()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            abort(403, 'Access denied');
        }
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        $topPerformers = $analytics['top_performers'];
        
        return view('analytics.top-performers', compact('topPerformers'));
    }

    /**
     * Generate analytics report
     */
    public function generateReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $format = $request->get('format', 'json');
        $sections = $request->get('sections', ['overview']);
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        
        $report = [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => $user->name,
            'sections' => [],
        ];
        
        foreach ($sections as $section) {
            if (isset($analytics[$section])) {
                $report['sections'][$section] = $analytics[$section];
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
    public function export(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $format = $request->get('format', 'csv');
        $section = $request->get('section', 'overview');
        
        $analytics = $this->analyticsService->getDashboardAnalytics();
        
        if (!isset($analytics[$section])) {
            return response()->json(['error' => 'Invalid section'], 400);
        }
        
        $data = $analytics[$section];
        
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
    public function clearCache()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $this->analyticsService->clearCache();
        
        return response()->json([
            'success' => true,
            'message' => 'Analytics cache cleared successfully',
        ]);
    }

    /**
     * Get real-time analytics
     */
    public function realTime()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        // Get real-time data (last 24 hours)
        $recentFiles = \App\Models\File::where('created_at', '>', now()->subDay())->count();
        $recentUsers = \App\Models\User::where('created_at', '>', now()->subDay())->count();
        $recentSearches = \App\Models\RecentActivity::where('action', 'search')
            ->where('created_at', '>', now()->subDay())
            ->count();
        $activeUsers = \App\Models\User::where('last_login_at', '>', now()->subHours(1))->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'recent_files' => $recentFiles,
                'recent_users' => $recentUsers,
                'recent_searches' => $recentSearches,
                'active_users' => $activeUsers,
                'timestamp' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }
} 