<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\OcrResult;
use App\Services\OcrService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class OcrController extends Controller
{
    protected $ocrService;
    protected $permissionService;

    public function __construct(OcrService $ocrService, PermissionService $permissionService)
    {
        $this->ocrService = $ocrService;
        $this->permissionService = $permissionService;
    }

    /**
     * Process OCR for a file
     */
    public function processOcr(Request $request, File $file): JsonResponse
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        // Check if file is suitable for OCR
        if (!$this->ocrService->isOcrSuitable($file)) {
            return response()->json(['error' => 'File type not suitable for OCR processing'], 400);
        }
        
        $validator = Validator::make($request->all(), [
            'language' => 'nullable|string|max:10',
            'psm' => 'nullable|integer|min:0|max:13',
            'oem' => 'nullable|integer|min:0|max:3',
            'config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $options = $request->only(['language', 'psm', 'oem', 'config']);
            
            // Process OCR
            $ocrResult = $this->ocrService->processOcr($file, $options);
            
            return response()->json([
                'success' => true,
                'message' => 'OCR processing completed',
                'result' => [
                    'id' => $ocrResult->id,
                    'status' => $ocrResult->status,
                    'confidence_score' => $ocrResult->confidence_score,
                    'language' => $ocrResult->language,
                    'processing_time' => $ocrResult->processing_time,
                    'extracted_text' => $ocrResult->extracted_text,
                    'word_count' => $ocrResult->word_count,
                    'character_count' => $ocrResult->character_count,
                    'confidence_level' => $ocrResult->confidence_level,
                    'confidence_color' => $ocrResult->confidence_color,
                    'formatted_processing_time' => $ocrResult->formatted_processing_time,
                    'language_name' => $ocrResult->language_name,
                    'formatted_options' => $ocrResult->formatted_options,
                    'processed_at' => $ocrResult->processed_at->format('M j, Y g:i A'),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('OCR processing failed', [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'OCR processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get OCR result for a file
     */
    public function getOcrResult(File $file): JsonResponse
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $ocrResult = $this->ocrService->getOcrResult($file);
        
        if (!$ocrResult) {
            return response()->json(['error' => 'No OCR result found for this file'], 404);
        }
        
        return response()->json([
            'success' => true,
            'result' => [
                'id' => $ocrResult->id,
                'status' => $ocrResult->status,
                'confidence_score' => $ocrResult->confidence_score,
                'language' => $ocrResult->language,
                'processing_time' => $ocrResult->processing_time,
                'extracted_text' => $ocrResult->extracted_text,
                'word_count' => $ocrResult->word_count,
                'character_count' => $ocrResult->character_count,
                'confidence_level' => $ocrResult->confidence_level,
                'confidence_color' => $ocrResult->confidence_color,
                'formatted_processing_time' => $ocrResult->formatted_processing_time,
                'language_name' => $ocrResult->language_name,
                'formatted_options' => $ocrResult->formatted_options,
                'processed_at' => $ocrResult->processed_at->format('M j, Y g:i A'),
                'error_message' => $ocrResult->error_message,
            ]
        ]);
    }

    /**
     * Update OCR result
     */
    public function updateOcrResult(Request $request, OcrResult $ocrResult): JsonResponse
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $ocrResult->file, 'edit')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'extracted_text' => 'required|string',
            'confidence_score' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $success = $this->ocrService->updateOcrResult(
                $ocrResult,
                $request->input('extracted_text'),
                $request->input('confidence_score')
            );
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'OCR result updated successfully'
                ]);
            } else {
                return response()->json(['error' => 'Failed to update OCR result'], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('OCR result update failed', [
                'ocr_result_id' => $ocrResult->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to update OCR result: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete OCR result
     */
    public function deleteOcrResult(OcrResult $ocrResult): JsonResponse
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $ocrResult->file, 'delete')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $success = $this->ocrService->deleteOcrResult($ocrResult);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'OCR result deleted successfully'
                ]);
            } else {
                return response()->json(['error' => 'Failed to delete OCR result'], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('OCR result deletion failed', [
                'ocr_result_id' => $ocrResult->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to delete OCR result: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Process batch OCR
     */
    public function processBatchOcr(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'file_ids' => 'required|array|min:1',
            'file_ids.*' => 'integer|exists:files,id',
            'language' => 'nullable|string|max:10',
            'psm' => 'nullable|integer|min:0|max:13',
            'oem' => 'nullable|integer|min:0|max:3',
            'config' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $fileIds = $request->input('file_ids');
            $options = $request->only(['language', 'psm', 'oem', 'config']);
            
            // Check permissions for all files
            $files = File::whereIn('id', $fileIds)->get();
            foreach ($files as $file) {
                if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
                    return response()->json(['error' => "Access denied for file: {$file->name}"], 403);
                }
            }
            
            // Process batch OCR
            $results = $this->ocrService->processBatchOcr($fileIds, $options);
            
            return response()->json([
                'success' => true,
                'message' => 'Batch OCR processing completed',
                'results' => $results,
                'summary' => [
                    'total_files' => count($fileIds),
                    'successful' => count(array_filter($results, fn($r) => $r['status'] === 'completed')),
                    'failed' => count(array_filter($results, fn($r) => $r['status'] === 'failed')),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Batch OCR processing failed', [
                'user_id' => $user->id,
                'file_ids' => $request->input('file_ids'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Batch OCR processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get OCR statistics
     */
    public function getOcrStats(): JsonResponse
    {
        $user = Auth::user();
        
        // Only allow admin users to view statistics
        if ($user->type !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $stats = $this->ocrService->getOcrStats();
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get OCR statistics', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to get OCR statistics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): JsonResponse
    {
        try {
            $languages = $this->ocrService->getSupportedLanguages();
            
            return response()->json([
                'success' => true,
                'languages' => $languages
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get supported languages', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to get supported languages: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get OCR engine options
     */
    public function getOcrOptions(): JsonResponse
    {
        try {
            $options = $this->ocrService->getOcrOptions();
            
            return response()->json([
                'success' => true,
                'options' => $options
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get OCR options', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to get OCR options: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get OCR queue status
     */
    public function getQueueStatus(): JsonResponse
    {
        $user = Auth::user();
        
        // Only allow admin users to view queue status
        if ($user->type !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $status = $this->ocrService->getQueueStatus();
            
            return response()->json([
                'success' => true,
                'status' => $status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get OCR queue status', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to get OCR queue status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show OCR interface
     */
    public function showOcr(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            abort(403, 'Access denied');
        }
        
        // Check if file is suitable for OCR
        if (!$this->ocrService->isOcrSuitable($file)) {
            abort(400, 'File type not suitable for OCR processing');
        }
        
        $ocrResult = $this->ocrService->getOcrResult($file);
        $languages = $this->ocrService->getSupportedLanguages();
        $options = $this->ocrService->getOcrOptions();
        
        return view('files.ocr', compact('file', 'ocrResult', 'languages', 'options'));
    }
}
