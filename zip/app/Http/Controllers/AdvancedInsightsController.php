<?php

namespace App\Http\Controllers;

use App\Services\AdvancedInsightsService;
use App\Services\BusinessIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AdvancedInsightsController extends Controller
{
    protected $insightsService;
    protected $biService;

    public function __construct(AdvancedInsightsService $insightsService, BusinessIntelligenceService $biService)
    {
        $this->insightsService = $insightsService;
        $this->biService = $biService;
    }

    /**
     * Display the Advanced Insights dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $period = request('period', '30d');
        
        $data = [
            'insights' => $this->insightsService->generateInsights($user->id, $period),
            'optimization' => $this->insightsService->getOptimizationSuggestions($user->id, $period),
            'recommendations' => $this->insightsService->getRecommendations($user->id, $period),
            'action_items' => $this->insightsService->getActionItems($user->id, $period),
        ];
        
        return view('advanced-insights.dashboard', compact('data', 'period'));
    }

    /**
     * Get comprehensive insights for API.
     */
    public function insights(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $insights = $this->insightsService->generateInsights($user->id, $period);
        
        return response()->json([
            'success' => true,
            'insights' => $insights
        ]);
    }

    /**
     * Get optimization suggestions.
     */
    public function optimization(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $optimization = $this->insightsService->getOptimizationSuggestions($user->id, $period);
        
        return response()->json([
            'success' => true,
            'optimization' => $optimization
        ]);
    }

    /**
     * Get automated recommendations.
     */
    public function recommendations(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $recommendations = $this->insightsService->getRecommendations($user->id, $period);
        
        return response()->json([
            'success' => true,
            'recommendations' => $recommendations
        ]);
    }

    /**
     * Get action items.
     */
    public function actionItems(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $actionItems = $this->insightsService->getActionItems($user->id, $period);
        
        return response()->json([
            'success' => true,
            'action_items' => $actionItems
        ]);
    }

    /**
     * Get performance insights.
     */
    public function performance(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $insights = $this->insightsService->generateInsights($user->id, $period);
        
        return response()->json([
            'success' => true,
            'performance' => $insights['performance_insights'] ?? []
        ]);
    }

    /**
     * Get predictive insights.
     */
    public function predictions(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $insights = $this->insightsService->generateInsights($user->id, $period);
        
        return response()->json([
            'success' => true,
            'predictions' => $insights['predictive_insights'] ?? []
        ]);
    }

    /**
     * Export insights data.
     */
    public function export(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        $format = $request->get('format', 'json');
        $sections = $request->get('sections', ['insights', 'optimization', 'recommendations']);
        
        try {
            $exportPath = $this->insightsService->exportInsights($user->id, $period, $format, $sections);
            
            return response()->json([
                'success' => true,
                'message' => 'Insights data exported successfully',
                'download_url' => route('advanced-insights.download', basename($exportPath))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export insights data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download exported insights data.
     */
    public function download($filename)
    {
        $filePath = storage_path('app/insights-exports/' . $filename);
        
        if (!file_exists($filePath)) {
            abort(404, 'Export file not found');
        }
        
        return response()->download($filePath, $filename);
    }

    /**
     * Get insights summary.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $insights = $this->insightsService->generateInsights($user->id, $period);
        
        $summary = [
            'total_insights' => count($insights['performance_insights']['strengths'] ?? []) + 
                               count($insights['performance_insights']['weaknesses'] ?? []) + 
                               count($insights['performance_insights']['opportunities'] ?? []),
            'optimization_suggestions' => count($insights['optimization_suggestions']['storage'] ?? []) + 
                                        count($insights['optimization_suggestions']['processing'] ?? []) + 
                                        count($insights['optimization_suggestions']['workflow'] ?? []),
            'recommendations' => count($insights['recommendations']['immediate'] ?? []) + 
                               count($insights['recommendations']['short_term'] ?? []) + 
                               count($insights['recommendations']['long_term'] ?? []),
            'action_items' => count($insights['action_items']['critical'] ?? []) + 
                            count($insights['action_items']['important'] ?? []) + 
                            count($insights['action_items']['improvement'] ?? []),
            'performance_score' => $insights['performance_insights']['performance_score'] ?? 0
        ];
        
        return response()->json([
            'success' => true,
            'summary' => $summary
        ]);
    }

    /**
     * Get insights configuration.
     */
    public function config(): JsonResponse
    {
        $config = [
            'insight_thresholds' => [
                'performance_score' => 70,
                'engagement_rate' => 60,
                'processing_success' => 85
            ],
            'optimization_settings' => [
                'storage_threshold' => 10 * 1024 * 1024, // 10MB
                'ocr_success_threshold' => 90,
                'automation_threshold' => 50
            ],
            'recommendation_settings' => [
                'priority_levels' => ['critical', 'important', 'medium', 'low'],
                'timeframes' => ['immediate', 'short_term', 'long_term']
            ]
        ];
        
        return response()->json([
            'success' => true,
            'config' => $config
        ]);
    }

    /**
     * Update insights configuration.
     */
    public function updateConfig(Request $request): JsonResponse
    {
        $user = Auth::user();
        $config = $request->all();
        
        try {
            // Store user-specific insights configuration
            cache()->put("insights_config_{$user->id}", $config, 86400);
            
            return response()->json([
                'success' => true,
                'message' => 'Insights configuration updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update insights configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time insights.
     */
    public function realtime(): JsonResponse
    {
        $user = Auth::user();
        
        $realtimeInsights = [
            'current_performance' => $this->getCurrentPerformance($user->id),
            'active_optimizations' => $this->getActiveOptimizations($user->id),
            'pending_actions' => $this->getPendingActions($user->id),
            'system_health' => $this->getSystemHealth($user->id)
        ];
        
        return response()->json([
            'success' => true,
            'realtime' => $realtimeInsights
        ]);
    }

    // Private helper methods

    private function getCurrentPerformance($userId): array
    {
        $recentActivities = \App\Models\RecentActivity::where('user_id', $userId)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return [
            'recent_activity' => $recentActivities,
            'performance_trend' => $recentActivities > 10 ? 'high' : ($recentActivities > 5 ? 'medium' : 'low'),
            'last_updated' => now()->toISOString()
        ];
    }

    private function getActiveOptimizations($userId): array
    {
        return [
            'storage_optimization' => [
                'status' => 'active',
                'progress' => 75,
                'estimated_completion' => now()->addHours(2)
            ],
            'processing_optimization' => [
                'status' => 'pending',
                'progress' => 0,
                'estimated_completion' => now()->addDays(1)
            ]
        ];
    }

    private function getPendingActions($userId): array
    {
        return [
            [
                'action' => 'Review large files',
                'priority' => 'high',
                'deadline' => now()->addHours(4),
                'status' => 'pending'
            ],
            [
                'action' => 'Optimize processing workflows',
                'priority' => 'medium',
                'deadline' => now()->addDays(1),
                'status' => 'pending'
            ]
        ];
    }

    private function getSystemHealth($userId): array
    {
        return [
            'overall_health' => 'excellent',
            'storage_health' => 'good',
            'processing_health' => 'excellent',
            'user_activity_health' => 'good',
            'last_check' => now()->toISOString()
        ];
    }
}
