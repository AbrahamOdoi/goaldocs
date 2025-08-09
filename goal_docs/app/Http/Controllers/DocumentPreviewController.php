<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Services\DocumentPreviewService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentPreviewController extends Controller
{
    protected $previewService;
    protected $permissionService;

    public function __construct(DocumentPreviewService $previewService, PermissionService $permissionService)
    {
        $this->previewService = $previewService;
        $this->permissionService = $permissionService;
    }

    /**
     * Show document preview page
     */
    public function show(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            abort(403, 'Access denied');
        }
        
        // Generate preview if not exists
        $preview = $this->getOrGeneratePreview($file);
        
        // Get file metadata
        $metadata = $this->getFileMetadata($file);
        
        // Get related files
        $relatedFiles = $this->getRelatedFiles($file);
        
        // Log preview access
        $this->logPreviewAccess($user, $file);
        
        return view('files.preview', compact('file', 'preview', 'metadata', 'relatedFiles'));
    }

    /**
     * AJAX endpoint for preview data
     */
    public function getPreviewData(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $preview = $this->getOrGeneratePreview($file);
        
        return response()->json([
            'success' => true,
            'preview' => $preview,
            'file' => [
                'id' => $file->id,
                'name' => $file->name,
                'size' => $file->human_size,
                'type' => $file->mime_type,
                'uploaded_at' => $file->created_at->format('M j, Y g:i A'),
                'uploaded_by' => $file->uploader->name ?? 'Unknown',
            ]
        ]);
    }

    /**
     * Get full text content for text-based files
     */
    public function getFullText(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $preview = $this->getOrGeneratePreview($file);
        
        if ($preview['type'] === 'text' || $preview['type'] === 'office') {
            return response()->json([
                'success' => true,
                'content' => $preview['full_content'] ?? $preview['content'],
                'total_length' => $preview['total_length'] ?? 0,
            ]);
        }
        
        return response()->json(['error' => 'Full text not available for this file type'], 400);
    }

    /**
     * Get PDF page as image
     */
    public function getPdfPage(File $file, Request $request)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            abort(403, 'Access denied');
        }
        
        $page = $request->get('page', 1);
        
        if ($file->mime_type !== 'application/pdf') {
            abort(400, 'File is not a PDF');
        }
        
        try {
            $path = Storage::disk('local')->path($file->file_path);
            $previewPath = 'previews/' . $file->id . '_page_' . $page . '.jpg';
            $fullPreviewPath = Storage::disk('local')->path($previewPath);
            
            // Create previews directory if it doesn't exist
            if (!file_exists(dirname($fullPreviewPath))) {
                mkdir(dirname($fullPreviewPath), 0755, true);
            }
            
            // Convert page to image
            $pdf = new \Spatie\PdfToImage\Pdf($path);
            $pdf->setPage($page)
                ->setOutputFormat('jpg')
                ->setResolution(150)
                ->saveImage($fullPreviewPath);
            
            return response()->file($fullPreviewPath);
        } catch (\Exception $e) {
            abort(500, 'Error generating PDF page preview');
        }
    }

    /**
     * Download preview file
     */
    public function downloadPreview(File $file)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->userHasPermission($user, $file, 'view')) {
            abort(403, 'Access denied');
        }
        
        $preview = $this->getOrGeneratePreview($file);
        
        if (isset($preview['preview_path'])) {
            $path = Storage::disk('local')->path($preview['preview_path']);
            if (file_exists($path)) {
                return response()->download($path, $file->name . '_preview.jpg');
            }
        }
        
        abort(404, 'Preview not found');
    }

    /**
     * Get or generate preview for file
     */
    private function getOrGeneratePreview(File $file)
    {
        // Check if preview already exists
        if ($file->search_metadata && isset($file->search_metadata['preview'])) {
            return $file->search_metadata['preview'];
        }
        
        // Generate new preview
        $preview = $this->previewService->generatePreview($file);
        
        // Save preview metadata
        $searchMetadata = $file->search_metadata ?? [];
        $searchMetadata['preview'] = $preview;
        
        $file->update([
            'search_metadata' => $searchMetadata,
            'indexed_at' => now(),
        ]);
        
        return $preview;
    }

    /**
     * Get file metadata
     */
    private function getFileMetadata(File $file): array
    {
        $metadata = [
            'basic' => [
                'Name' => $file->name,
                'Original Name' => $file->original_name,
                'Size' => $file->human_size,
                'Type' => $file->mime_type,
                'Extension' => $file->extension,
                'Uploaded' => $file->created_at->format('M j, Y g:i A'),
                'Last Modified' => $file->updated_at->format('M j, Y g:i A'),
                'Uploaded By' => $file->uploader->name ?? 'Unknown',
            ]
        ];
        
        // Add preview-specific metadata
        $preview = $this->getOrGeneratePreview($file);
        if (isset($preview['metadata'])) {
            $metadata['preview'] = $preview['metadata'];
        }
        
        // Add file tags
        if ($file->tags->count() > 0) {
            $metadata['tags'] = $file->tags->pluck('tag_name')->toArray();
        }
        
        // Add version information
        if ($file->versions->count() > 0) {
            $metadata['versions'] = [
                'total_versions' => $file->versions->count(),
                'current_version' => $file->versions->where('is_current', true)->first()->version_number ?? 1,
            ];
        }
        
        return $metadata;
    }

    /**
     * Get related files
     */
    private function getRelatedFiles(File $file): array
    {
        $related = [];
        
        // Files in same folder
        if ($file->folder) {
            $related['same_folder'] = File::where('folder_id', $file->folder_id)
                ->where('id', '!=', $file->id)
                ->where('is_active', true)
                ->limit(5)
                ->get();
        }
        
        // Files with same extension
        $related['same_type'] = File::where('extension', $file->extension)
            ->where('id', '!=', $file->id)
            ->where('is_active', true)
            ->where('user_type', $file->user_type)
            ->where('type_name', $file->type_name)
            ->limit(5)
            ->get();
        
        // Files uploaded by same user
        $related['same_uploader'] = File::where('uploaded_by', $file->uploaded_by)
            ->where('id', '!=', $file->id)
            ->where('is_active', true)
            ->limit(5)
            ->get();
        
        return $related;
    }

    /**
     * Log preview access
     */
    private function logPreviewAccess($user, $file): void
    {
        // Update last accessed timestamp
        $file->updateLastAccessed();
        
        // Log activity
        \App\Models\RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'preview',
            $file->id,
            null,
            ['file_name' => $file->name, 'preview_type' => 'document']
        );
    }

    /**
     * Batch generate previews
     */
    public function batchGenerate(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $fileIds = $request->input('file_ids', []);
        
        if (empty($fileIds)) {
            return response()->json(['error' => 'No files specified'], 400);
        }
        
        $results = $this->previewService->batchGeneratePreviews($fileIds);
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => "Processed {$results['success']} files successfully, {$results['failed']} failed"
        ]);
    }

    /**
     * Get preview statistics
     */
    public function getStats()
    {
        $user = Auth::user();
        
        if (!$user->is_admin) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        $stats = [
            'total_files' => File::count(),
            'files_with_preview' => File::whereNotNull('search_metadata->preview')->count(),
            'files_without_preview' => File::whereNull('search_metadata->preview')->count(),
            'preview_types' => [],
        ];
        
        // Get preview type distribution
        $filesWithPreview = File::whereNotNull('search_metadata->preview')->get();
        foreach ($filesWithPreview as $file) {
            $previewType = $file->search_metadata['preview']['type'] ?? 'unknown';
            $stats['preview_types'][$previewType] = ($stats['preview_types'][$previewType] ?? 0) + 1;
        }
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
} 