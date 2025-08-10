<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\BusinessIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class BusinessIntelligenceController extends Controller
{
    protected $analyticsService;
    protected $biService;

    public function __construct(AnalyticsService $analyticsService, BusinessIntelligenceService $biService)
    {
        $this->analyticsService = $analyticsService;
        $this->biService = $biService;
    }

    /**
     * Display the Business Intelligence dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();
        $period = request('period', '30d');
        
        $data = [
            'kpis' => $this->biService->getKPIs($user->id, $period),
            'trends' => $this->biService->getTrends($user->id, $period),
            'comparisons' => $this->biService->getComparisons($user->id, $period),
            'insights' => $this->biService->getInsights($user->id, $period),
            'predictions' => $this->biService->getPredictions($user->id, $period),
            'anomalies' => $this->biService->getAnomalies($user->id, $period),
        ];
        
        return view('business-intelligence.dashboard', compact('data', 'period'));
    }

    /**
     * Get KPI data for API.
     */
    public function kpis(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $kpis = $this->biService->getKPIs($user->id, $period);
        
        return response()->json([
            'success' => true,
            'kpis' => $kpis
        ]);
    }

    /**
     * Get trend analysis data.
     */
    public function trends(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        $metric = $request->get('metric', 'all');
        
        $trends = $this->biService->getTrends($user->id, $period, $metric);
        
        return response()->json([
            'success' => true,
            'trends' => $trends
        ]);
    }

    /**
     * Get comparative analysis data.
     */
    public function comparisons(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        $comparisonType = $request->get('type', 'period');
        
        $comparisons = $this->biService->getComparisons($user->id, $period, $comparisonType);
        
        return response()->json([
            'success' => true,
            'comparisons' => $comparisons
        ]);
    }

    /**
     * Get business insights.
     */
    public function insights(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        $category = $request->get('category', 'all');
        
        $insights = $this->biService->getInsights($user->id, $period, $category);
        
        return response()->json([
            'success' => true,
            'insights' => $insights
        ]);
    }

    /**
     * Get predictive analytics data.
     */
    public function predictions(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        $forecastPeriod = $request->get('forecast', '7d');
        
        $predictions = $this->biService->getPredictions($user->id, $period, $forecastPeriod);
        
        return response()->json([
            'success' => true,
            'predictions' => $predictions
        ]);
    }

    /**
     * Get anomaly detection data.
     */
    public function anomalies(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        $severity = $request->get('severity', 'all');
        
        $anomalies = $this->biService->getAnomalies($user->id, $period, $severity);
        
        return response()->json([
            'success' => true,
            'anomalies' => $anomalies
        ]);
    }

    /**
     * Get real-time BI data.
     */
    public function realtime(): JsonResponse
    {
        $user = Auth::user();
        
        $realtimeData = $this->biService->getRealTimeData($user->id);
        
        return response()->json([
            'success' => true,
            'realtime' => $realtimeData
        ]);
    }

    /**
     * Get BI summary data.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        
        $summary = $this->biService->getSummary($user->id, $period);
        
        return response()->json([
            'success' => true,
            'summary' => $summary
        ]);
    }

    /**
     * Export BI data.
     */
    public function export(Request $request): JsonResponse
    {
        $user = Auth::user();
        $period = $request->get('period', '30d');
        $format = $request->get('format', 'pdf');
        $sections = $request->get('sections', ['kpis', 'trends', 'insights']);
        
        try {
            $exportPath = $this->biService->exportData($user->id, $period, $format, $sections);
            
            return response()->json([
                'success' => true,
                'message' => 'BI data exported successfully',
                'download_url' => route('bi.download', basename($exportPath))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export BI data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download exported BI data.
     */
    public function download($filename)
    {
        $filePath = storage_path('app/bi-exports/' . $filename);
        
        if (!file_exists($filePath)) {
            abort(404, 'Export file not found');
        }
        
        return response()->download($filePath, $filename);
    }

    /**
     * Get BI configuration.
     */
    public function config(): JsonResponse
    {
        $config = $this->biService->getConfiguration();
        
        return response()->json([
            'success' => true,
            'config' => $config
        ]);
    }

    /**
     * Update BI configuration.
     */
    public function updateConfig(Request $request): JsonResponse
    {
        $user = Auth::user();
        $config = $request->all();
        
        try {
            $this->biService->updateConfiguration($user->id, $config);
            
            return response()->json([
                'success' => true,
                'message' => 'BI configuration updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update BI configuration: ' . $e->getMessage()
            ], 500);
        }
    }
}
