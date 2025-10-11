<?php

namespace App\Http\Controllers;

use App\Models\BatchJob;
use App\Models\File;
use App\Services\BatchProcessingService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class BatchProcessingController extends Controller
{
    protected $batchProcessingService;
    protected $permissionService;

    public function __construct(BatchProcessingService $batchProcessingService, PermissionService $permissionService)
    {
        $this->batchProcessingService = $batchProcessingService;
        $this->permissionService = $permissionService;
    }

    /**
     * Show batch processing dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $batchJobs = BatchJob::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = $this->batchProcessingService->getBatchProcessingStats();
        $queueStatus = $this->batchProcessingService->getQueueStatus();
        $supportedOperations = $this->batchProcessingService->getSupportedOperationTypes();

        return view('batch-processing.index', compact('batchJobs', 'stats', 'queueStatus', 'supportedOperations'));
    }

    /**
     * Show batch processing form
     */
    public function create()
    {
        $user = Auth::user();
        $files = File::where('uploaded_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $supportedOperations = $this->batchProcessingService->getSupportedOperationTypes();

        return view('batch-processing.create', compact('files', 'supportedOperations'));
    }

    /**
     * Create a new batch job
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'file_ids' => 'required|array|min:1',
            'file_ids.*' => 'integer|exists:files,id',
            'operation_type' => 'required|string|in:' . implode(',', array_keys($this->batchProcessingService->getSupportedOperationTypes())),
            'options' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            // Validate file access permissions
            $fileIds = $request->input('file_ids');
            $files = File::whereIn('id', $fileIds)->get();
            
            foreach ($files as $file) {
                if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
                    return response()->json(['error' => "Access denied to file: {$file->name}"], 403);
                }
            }

            // Validate operation-specific options
            $options = $request->input('options', []);
            $validationErrors = $this->batchProcessingService->validateBatchJobOptions(
                $request->input('operation_type'),
                $options
            );

            if (!empty($validationErrors)) {
                return response()->json(['error' => implode(', ', $validationErrors)], 400);
            }

            // Create batch job
            $batchJob = $this->batchProcessingService->createBatchJob(
                $fileIds,
                $request->input('operation_type'),
                $options
            );

            return response()->json([
                'success' => true,
                'message' => 'Batch job created successfully',
                'batch_job_id' => $batchJob->id,
                'redirect_url' => route('batch-processing.show', $batchJob)
            ]);

        } catch (\Exception $e) {
            Log::error('Batch job creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to create batch job: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show batch job details
     */
    public function show(BatchJob $batchJob)
    {
        $user = Auth::user();

        // Check if user owns this batch job
        if ($batchJob->user_id !== $user->id) {
            abort(403, 'Access denied');
        }

        $status = $this->batchProcessingService->getBatchJobStatus($batchJob);
        $logs = $this->batchProcessingService->getBatchJobLogs($batchJob, 100);
        $files = $batchJob->files;

        return view('batch-processing.show', compact('batchJob', 'status', 'logs', 'files'));
    }

    /**
     * Start processing a batch job
     */
    public function start(BatchJob $batchJob): JsonResponse
    {
        $user = Auth::user();

        // Check if user owns this batch job
        if ($batchJob->user_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if job can be started
        if (!$batchJob->isPending()) {
            return response()->json(['error' => 'Batch job cannot be started. Current status: ' . $batchJob->status], 400);
        }

        try {
            $success = $this->batchProcessingService->startBatchJob($batchJob);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Batch job started successfully',
                    'status' => $batchJob->fresh()->status
                ]);
            } else {
                return response()->json(['error' => 'Failed to start batch job'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Batch job start failed', [
                'batch_job_id' => $batchJob->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to start batch job: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cancel a batch job
     */
    public function cancel(BatchJob $batchJob): JsonResponse
    {
        $user = Auth::user();

        // Check if user owns this batch job
        if ($batchJob->user_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $success = $this->batchProcessingService->cancelBatchJob($batchJob);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Batch job cancelled successfully',
                    'status' => $batchJob->fresh()->status
                ]);
            } else {
                return response()->json(['error' => 'Batch job cannot be cancelled'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Batch job cancellation failed', [
                'batch_job_id' => $batchJob->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to cancel batch job: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a batch job
     */
    public function destroy(BatchJob $batchJob): JsonResponse
    {
        $user = Auth::user();

        // Check if user owns this batch job
        if ($batchJob->user_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $success = $this->batchProcessingService->deleteBatchJob($batchJob);

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Batch job deleted successfully',
                    'redirect_url' => route('batch-processing.index')
                ]);
            } else {
                return response()->json(['error' => 'Failed to delete batch job'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Batch job deletion failed', [
                'batch_job_id' => $batchJob->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to delete batch job: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get batch job status (AJAX)
     */
    public function getStatus(BatchJob $batchJob): JsonResponse
    {
        $user = Auth::user();

        // Check if user owns this batch job
        if ($batchJob->user_id !== $user->id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $status = $this->batchProcessingService->getBatchJobStatus($batchJob);
        $logs = $this->batchProcessingService->getBatchJobLogs($batchJob, 10);

        return response()->json([
            'success' => true,
            'status' => $status,
            'logs' => $logs,
            'is_completed' => $batchJob->isCompleted(),
            'is_failed' => $batchJob->isFailed(),
            'is_cancelled' => $batchJob->isCancelled(),
        ]);
    }

    /**
     * Get batch processing statistics (AJAX)
     */
    public function getStats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->batchProcessingService->getBatchProcessingStats();
        $queueStatus = $this->batchProcessingService->getQueueStatus();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'queue_status' => $queueStatus,
        ]);
    }

    /**
     * Get supported operation types (AJAX)
     */
    public function getSupportedOperations(): JsonResponse
    {
        $supportedOperations = $this->batchProcessingService->getSupportedOperationTypes();

        return response()->json([
            'success' => true,
            'operations' => $supportedOperations,
        ]);
    }

    /**
     * Get user's files for batch processing (AJAX)
     */
    public function getUserFiles(): JsonResponse
    {
        $user = Auth::user();
        
        $files = File::where('uploaded_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'original_name', 'mime_type', 'file_size', 'created_at'])
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->name,
                    'original_name' => $file->original_name,
                    'mime_type' => $file->mime_type,
                    'file_size' => $file->file_size,
                    'formatted_size' => $this->formatFileSize($file->file_size),
                    'created_at' => $file->created_at->format('M j, Y g:i A'),
                ];
            });

        return response()->json([
            'success' => true,
            'files' => $files,
        ]);
    }

    /**
     * Format file size for display
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
