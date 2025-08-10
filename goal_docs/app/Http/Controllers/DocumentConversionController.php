<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\DocumentConversion;
use App\Services\DocumentConversionService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentConversionController extends Controller
{
    protected $conversionService;
    protected $permissionService;

    public function __construct(DocumentConversionService $conversionService, PermissionService $permissionService)
    {
        $this->conversionService = $conversionService;
        $this->permissionService = $permissionService;
    }

    /**
     * Convert a file to a different format
     */
    public function convertFile(Request $request, File $file): JsonResponse
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'target_format' => 'required|string',
            'quality' => 'nullable|integer|min:1|max:100',
            'max_width' => 'nullable|integer|min:1',
            'max_height' => 'nullable|integer|min:1',
            'rotate' => 'nullable|integer|min:-360|max:360',
            'flip' => 'nullable|string|in:horizontal,vertical',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            $targetFormat = $request->input('target_format');
            $options = $request->only(['quality', 'max_width', 'max_height', 'rotate', 'flip']);
            
            // Check if conversion is supported
            if (!$this->conversionService->isConversionSupported($file, $targetFormat)) {
                return response()->json(['error' => 'Conversion not supported for this file type'], 400);
            }
            
            // Perform conversion
            $conversion = $this->conversionService->convertFile($file, $targetFormat, $options);
            
            return response()->json([
                'success' => true,
                'message' => 'Document conversion completed',
                'conversion' => [
                    'id' => $conversion->id,
                    'status' => $conversion->status,
                    'conversion_type' => $conversion->conversion_type,
                    'conversion_type_description' => $conversion->conversion_type_description,
                    'source_format' => $conversion->source_format,
                    'target_format' => $conversion->target_format,
                    'source_extension' => $conversion->source_extension,
                    'target_extension' => $conversion->target_extension,
                    'processing_time' => $conversion->processing_time,
                    'quality_score' => $conversion->quality_score,
                    'quality_level' => $conversion->quality_level,
                    'quality_color' => $conversion->quality_color,
                    'formatted_processing_time' => $conversion->formatted_processing_time,
                    'output_file_size' => $conversion->output_file_size,
                    'human_output_size' => $conversion->human_output_size,
                    'download_url' => $conversion->download_url,
                    'preview_url' => $conversion->preview_url,
                    'formatted_options' => $conversion->formatted_options,
                    'duration' => $conversion->duration,
                    'started_at' => $conversion->started_at?->format('M j, Y g:i A'),
                    'completed_at' => $conversion->completed_at?->format('M j, Y g:i A'),
                    'error_message' => $conversion->error_message,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Document conversion failed', [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'target_format' => $request->input('target_format'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Document conversion failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get conversion result for a file
     */
    public function getConversionResult(File $file, string $targetFormat): JsonResponse
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $conversion = $this->conversionService->getConversionResult($file, $targetFormat);
        
        if (!$conversion) {
            return response()->json(['error' => 'No conversion result found for this file and format'], 404);
        }
        
        return response()->json([
            'success' => true,
            'conversion' => [
                'id' => $conversion->id,
                'status' => $conversion->status,
                'conversion_type' => $conversion->conversion_type,
                'conversion_type_description' => $conversion->conversion_type_description,
                'source_format' => $conversion->source_format,
                'target_format' => $conversion->target_format,
                'source_extension' => $conversion->source_extension,
                'target_extension' => $conversion->target_extension,
                'processing_time' => $conversion->processing_time,
                'quality_score' => $conversion->quality_score,
                'quality_level' => $conversion->quality_level,
                'quality_color' => $conversion->quality_color,
                'formatted_processing_time' => $conversion->formatted_processing_time,
                'output_file_size' => $conversion->output_file_size,
                'human_output_size' => $conversion->human_output_size,
                'download_url' => $conversion->download_url,
                'preview_url' => $conversion->preview_url,
                'formatted_options' => $conversion->formatted_options,
                'duration' => $conversion->duration,
                'started_at' => $conversion->started_at?->format('M j, Y g:i A'),
                'completed_at' => $conversion->completed_at?->format('M j, Y g:i A'),
                'error_message' => $conversion->error_message,
            ]
        ]);
    }

    /**
     * Get supported conversions for a file
     */
    public function getSupportedConversions(File $file): JsonResponse
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $supportedConversions = $this->conversionService->getSupportedConversions($file);
            
            return response()->json([
                'success' => true,
                'supported_conversions' => $supportedConversions,
                'file_info' => [
                    'id' => $file->id,
                    'name' => $file->name,
                    'mime_type' => $file->mime_type,
                    'extension' => $file->extension,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get supported conversions', [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to get supported conversions: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get conversion history for a file
     */
    public function getConversionHistory(File $file): JsonResponse
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $conversions = DocumentConversion::where('file_id', $file->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            $conversionHistory = $conversions->map(function ($conversion) {
                return [
                    'id' => $conversion->id,
                    'status' => $conversion->status,
                    'conversion_type' => $conversion->conversion_type,
                    'conversion_type_description' => $conversion->conversion_type_description,
                    'source_format' => $conversion->source_format,
                    'target_format' => $conversion->target_format,
                    'source_extension' => $conversion->source_extension,
                    'target_extension' => $conversion->target_extension,
                    'processing_time' => $conversion->processing_time,
                    'quality_score' => $conversion->quality_score,
                    'quality_level' => $conversion->quality_level,
                    'quality_color' => $conversion->quality_color,
                    'formatted_processing_time' => $conversion->formatted_processing_time,
                    'output_file_size' => $conversion->output_file_size,
                    'human_output_size' => $conversion->human_output_size,
                    'download_url' => $conversion->download_url,
                    'preview_url' => $conversion->preview_url,
                    'formatted_options' => $conversion->formatted_options,
                    'duration' => $conversion->duration,
                    'started_at' => $conversion->started_at?->format('M j, Y g:i A'),
                    'completed_at' => $conversion->completed_at?->format('M j, Y g:i A'),
                    'error_message' => $conversion->error_message,
                    'created_at' => $conversion->created_at->format('M j, Y g:i A'),
                ];
            });
            
            return response()->json([
                'success' => true,
                'conversion_history' => $conversionHistory,
                'total_conversions' => $conversions->count(),
                'successful_conversions' => $conversions->where('status', 'completed')->count(),
                'failed_conversions' => $conversions->where('status', 'failed')->count(),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get conversion history', [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to get conversion history: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a conversion result
     */
    public function deleteConversion(DocumentConversion $conversion): JsonResponse
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $conversion->file, 'delete')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            // Delete the output file if it exists
            if ($conversion->output_file_path && Storage::exists($conversion->output_file_path)) {
                Storage::delete($conversion->output_file_path);
            }
            
            // Delete the conversion record
            $conversion->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Conversion deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to delete conversion', [
                'conversion_id' => $conversion->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to delete conversion: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download converted file
     */
    public function downloadConvertedFile(DocumentConversion $conversion)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $conversion->file, 'download')) {
            abort(403, 'Access denied');
        }
        
        // Check if conversion was successful
        if (!$conversion->isSuccessful() || !$conversion->output_file_path) {
            abort(404, 'Converted file not found');
        }
        
        // Check if file exists
        if (!Storage::exists($conversion->output_file_path)) {
            abort(404, 'Converted file not found on disk');
        }
        
        $filename = pathinfo($conversion->file->name, PATHINFO_FILENAME) . '_converted.' . $conversion->target_extension;
        
        return Storage::download($conversion->output_file_path, $filename);
    }

    /**
     * Preview converted file
     */
    public function previewConvertedFile(DocumentConversion $conversion)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $conversion->file, 'view')) {
            abort(403, 'Access denied');
        }
        
        // Check if conversion was successful
        if (!$conversion->isSuccessful() || !$conversion->output_file_path) {
            abort(404, 'Converted file not found');
        }
        
        // Check if file exists
        if (!Storage::exists($conversion->output_file_path)) {
            abort(404, 'Converted file not found on disk');
        }
        
        // For now, redirect to download (in a real app, you'd implement preview)
        return redirect()->route('files.conversion.download', $conversion->id);
    }

    /**
     * Get conversion statistics
     */
    public function getConversionStats(): JsonResponse
    {
        $user = Auth::user();
        
        // Only allow admin users to view statistics
        if ($user->type !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $stats = $this->conversionService->getConversionStats();
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get conversion statistics', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to get conversion statistics: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get conversion queue status
     */
    public function getQueueStatus(): JsonResponse
    {
        $user = Auth::user();
        
        // Only allow admin users to view queue status
        if ($user->type !== 'admin') {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        try {
            $status = $this->conversionService->getQueueStatus();
            
            return response()->json([
                'success' => true,
                'status' => $status
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get conversion queue status', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Failed to get conversion queue status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show conversion interface
     */
    public function showConversion(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            abort(403, 'Access denied');
        }
        
        $supportedConversions = $this->conversionService->getSupportedConversions($file);
        $conversionHistory = DocumentConversion::where('file_id', $file->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('files.conversion', compact('file', 'supportedConversions', 'conversionHistory'));
    }
}
