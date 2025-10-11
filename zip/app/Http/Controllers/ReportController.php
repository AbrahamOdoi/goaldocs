<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ReportGeneration;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportController extends Controller
{
    use AuthorizesRequests;
    
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the reports dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $reports = $this->reportService->getUserReports($user);
        $publicReports = $this->reportService->getPublicReports();
        
        return view('reports.index', compact('reports', 'publicReports'));
    }

    /**
     * Show the report builder interface.
     */
    public function create()
    {
        $reportTypes = Report::getTypeOptions();
        $scheduleOptions = Report::getScheduleOptions();
        
        return view('reports.create', compact('reportTypes', 'scheduleOptions'));
    }

    /**
     * Store a new report.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(Report::getTypeOptions())),
            'config' => 'nullable|array',
            'schedule' => 'nullable|array',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $report = $this->reportService->createReport($request->all(), Auth::user());
            
            return response()->json([
                'success' => true,
                'message' => 'Report created successfully',
                'report' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified report.
     */
    public function show(Report $report)
    {
        $this->authorize('view', $report);
        
        $generations = $report->generations()->orderBy('created_at', 'desc')->paginate(10);
        
        return view('reports.show', compact('report', 'generations'));
    }

    /**
     * Show the form for editing the specified report.
     */
    public function edit(Report $report)
    {
        $this->authorize('update', $report);
        
        $reportTypes = Report::getTypeOptions();
        $scheduleOptions = Report::getScheduleOptions();
        
        return view('reports.edit', compact('report', 'reportTypes', 'scheduleOptions'));
    }

    /**
     * Update the specified report.
     */
    public function update(Request $request, Report $report): JsonResponse
    {
        $this->authorize('update', $report);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(Report::getTypeOptions())),
            'config' => 'nullable|array',
            'schedule' => 'nullable|array',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $report = $this->reportService->updateReport($report, $request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Report updated successfully',
                'report' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified report.
     */
    public function destroy(Report $report): JsonResponse
    {
        $this->authorize('delete', $report);
        
        try {
            $this->reportService->deleteReport($report);
            
            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate a report.
     */
    public function generate(Request $request, Report $report): JsonResponse
    {
        $this->authorize('generate', $report);
        
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:pdf,excel,csv'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $generation = $this->reportService->generateReport(
                $report, 
                Auth::user(), 
                $request->format
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Report generation started',
                'generation' => $generation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a generated report.
     */
    public function download(ReportGeneration $generation)
    {
        $this->authorize('download', $generation->report);
        
        if (!$generation->isCompleted() || !$generation->file_path) {
            abort(404, 'Report file not found');
        }
        
        if (!Storage::disk('local')->exists($generation->file_path)) {
            abort(404, 'Report file not found');
        }
        
        $fileName = $generation->report->name . '.' . $generation->format;
        
        return response()->download(Storage::disk('local')->path($generation->file_path), $fileName);
    }

    /**
     * Get generation status.
     */
    public function status(ReportGeneration $generation): JsonResponse
    {
        $this->authorize('view', $generation->report);
        
        return response()->json([
            'success' => true,
            'generation' => $generation
        ]);
    }

    /**
     * Get reports for API.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $user = Auth::user();
        $filters = $request->only(['type', 'is_active', 'is_public']);
        
        $reports = $this->reportService->getUserReports($user, $filters);
        
        return response()->json([
            'success' => true,
            'reports' => $reports
        ]);
    }

    /**
     * Get public reports for API.
     */
    public function apiPublic(): JsonResponse
    {
        $reports = $this->reportService->getPublicReports();
        
        return response()->json([
            'success' => true,
            'reports' => $reports
        ]);
    }

    /**
     * Get report statistics.
     */
    public function stats(): JsonResponse
    {
        $user = Auth::user();
        
        $stats = [
            'total_reports' => Report::where('user_id', $user->id)->count(),
            'active_reports' => Report::where('user_id', $user->id)->active()->count(),
            'scheduled_reports' => Report::where('user_id', $user->id)->scheduled()->count(),
            'public_reports' => Report::where('user_id', $user->id)->public()->count(),
            'total_generations' => ReportGeneration::where('user_id', $user->id)->count(),
            'completed_generations' => ReportGeneration::where('user_id', $user->id)->completed()->count(),
            'failed_generations' => ReportGeneration::where('user_id', $user->id)->failed()->count(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
