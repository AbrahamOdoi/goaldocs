<?php

namespace App\Services;

use App\Models\File;
use App\Models\BatchJob;
use App\Models\RecentActivity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BatchProcessingService
{
    /**
     * Supported batch operation types
     */
    const OPERATION_TYPES = [
        'ocr' => 'OCR Processing',
        'conversion' => 'Format Conversion',
        'text_extraction' => 'Text Extraction',
        'thumbnail_generation' => 'Thumbnail Generation',
        'metadata_extraction' => 'Metadata Extraction',
    ];

    /**
     * Create a new batch job
     */
    public function createBatchJob(array $fileIds, string $operationType, array $options = []): BatchJob
    {
        $user = auth()->user();
        
        // Validate operation type
        if (!array_key_exists($operationType, self::OPERATION_TYPES)) {
            throw new \Exception("Unsupported operation type: {$operationType}");
        }
        
        // Validate files exist and user has access
        $files = File::whereIn('id', $fileIds)->get();
        if ($files->count() !== count($fileIds)) {
            throw new \Exception("Some files not found or access denied");
        }
        
        // Create batch job record
        $batchJob = BatchJob::create([
            'user_id' => $user->id,
            'operation_type' => $operationType,
            'file_ids' => $fileIds,
            'total_files' => count($fileIds),
            'processed_files' => 0,
            'successful_files' => 0,
            'failed_files' => 0,
            'options' => $options,
            'status' => 'pending',
            'progress' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);
        
        // Log activity
        $this->logBatchJobActivity($batchJob, 'created');
        
        return $batchJob;
    }

    /**
     * Start processing a batch job
     */
    public function startBatchJob(BatchJob $batchJob): bool
    {
        try {
            $batchJob->update([
                'status' => 'processing',
                'started_at' => now(),
            ]);
            
            // Process files based on operation type
            switch ($batchJob->operation_type) {
                case 'ocr':
                    return $this->processOcrBatch($batchJob);
                    
                case 'conversion':
                    return $this->processConversionBatch($batchJob);
                    
                case 'text_extraction':
                    return $this->processTextExtractionBatch($batchJob);
                    
                case 'thumbnail_generation':
                    return $this->processThumbnailBatch($batchJob);
                    
                case 'metadata_extraction':
                    return $this->processMetadataBatch($batchJob);
                    
                default:
                    throw new \Exception("Unsupported operation type: {$batchJob->operation_type}");
            }
            
        } catch (\Exception $e) {
            Log::error('Batch job processing failed', [
                'batch_job_id' => $batchJob->id,
                'operation_type' => $batchJob->operation_type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $batchJob->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);
            
            return false;
        }
    }

    /**
     * Process OCR batch job
     */
    private function processOcrBatch(BatchJob $batchJob): bool
    {
        $ocrService = app(OcrService::class);
        $files = File::whereIn('id', $batchJob->file_ids)->get();
        
        foreach ($files as $index => $file) {
            try {
                // Check if file is suitable for OCR
                if (!$ocrService->isOcrSuitable($file)) {
                    $this->updateBatchJobProgress($batchJob, $index + 1, false, "File not suitable for OCR: {$file->name}");
                    continue;
                }
                
                // Process OCR
                $ocrResult = $ocrService->processOcr($file, $batchJob->options);
                
                if ($ocrResult->isSuccessful()) {
                    $this->updateBatchJobProgress($batchJob, $index + 1, true, "OCR completed for: {$file->name}");
                } else {
                    $this->updateBatchJobProgress($batchJob, $index + 1, false, "OCR failed for: {$file->name}");
                }
                
            } catch (\Exception $e) {
                $this->updateBatchJobProgress($batchJob, $index + 1, false, "OCR error for {$file->name}: " . $e->getMessage());
            }
        }
        
        $this->completeBatchJob($batchJob);
        return true;
    }

    /**
     * Process conversion batch job
     */
    private function processConversionBatch(BatchJob $batchJob): bool
    {
        $conversionService = app(DocumentConversionService::class);
        $files = File::whereIn('id', $batchJob->file_ids)->get();
        $targetFormat = $batchJob->options['target_format'] ?? null;
        
        if (!$targetFormat) {
            throw new \Exception("Target format not specified for conversion batch job");
        }
        
        foreach ($files as $index => $file) {
            try {
                // Check if conversion is supported
                if (!$conversionService->isConversionSupported($file, $targetFormat)) {
                    $this->updateBatchJobProgress($batchJob, $index + 1, false, "Conversion not supported for: {$file->name}");
                    continue;
                }
                
                // Process conversion
                $conversion = $conversionService->convertFile($file, $targetFormat, $batchJob->options);
                
                if ($conversion->isSuccessful()) {
                    $this->updateBatchJobProgress($batchJob, $index + 1, true, "Conversion completed for: {$file->name}");
                } else {
                    $this->updateBatchJobProgress($batchJob, $index + 1, false, "Conversion failed for: {$file->name}");
                }
                
            } catch (\Exception $e) {
                $this->updateBatchJobProgress($batchJob, $index + 1, false, "Conversion error for {$file->name}: " . $e->getMessage());
            }
        }
        
        $this->completeBatchJob($batchJob);
        return true;
    }

    /**
     * Process text extraction batch job
     */
    private function processTextExtractionBatch(BatchJob $batchJob): bool
    {
        $extractionService = app(AdvancedTextExtractionService::class);
        $files = File::whereIn('id', $batchJob->file_ids)->get();
        $extractionType = $batchJob->options['extraction_type'] ?? 'full_text';
        
        foreach ($files as $index => $file) {
            try {
                // Check if file is suitable for text extraction
                if (!$extractionService->isExtractionSuitable($file)) {
                    $this->updateBatchJobProgress($batchJob, $index + 1, false, "File not suitable for text extraction: {$file->name}");
                    continue;
                }
                
                // Process text extraction
                $extraction = $extractionService->extractText($file, [
                    'type' => $extractionType,
                    ...$batchJob->options
                ]);
                
                if ($extraction->isSuccessful()) {
                    $this->updateBatchJobProgress($batchJob, $index + 1, true, "Text extraction completed for: {$file->name}");
                } else {
                    $this->updateBatchJobProgress($batchJob, $index + 1, false, "Text extraction failed for: {$file->name}");
                }
                
            } catch (\Exception $e) {
                $this->updateBatchJobProgress($batchJob, $index + 1, false, "Text extraction error for {$file->name}: " . $e->getMessage());
            }
        }
        
        $this->completeBatchJob($batchJob);
        return true;
    }

    /**
     * Process thumbnail generation batch job
     */
    private function processThumbnailBatch(BatchJob $batchJob): bool
    {
        $fileController = app(\App\Http\Controllers\FileController::class);
        $files = File::whereIn('id', $batchJob->file_ids)->get();
        
        foreach ($files as $index => $file) {
            try {
                // Check if file supports thumbnail generation
                if (!in_array($file->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp', 'application/pdf'])) {
                    $this->updateBatchJobProgress($batchJob, $index + 1, false, "Thumbnail not supported for: {$file->name}");
                    continue;
                }
                
                // Generate thumbnail
                $thumbnailPath = $fileController->generateThumbnail($file);
                
                if ($thumbnailPath) {
                    $this->updateBatchJobProgress($batchJob, $index + 1, true, "Thumbnail generated for: {$file->name}");
                } else {
                    $this->updateBatchJobProgress($batchJob, $index + 1, false, "Thumbnail generation failed for: {$file->name}");
                }
                
            } catch (\Exception $e) {
                $this->updateBatchJobProgress($batchJob, $index + 1, false, "Thumbnail error for {$file->name}: " . $e->getMessage());
            }
        }
        
        $this->completeBatchJob($batchJob);
        return true;
    }

    /**
     * Process metadata extraction batch job
     */
    private function processMetadataBatch(BatchJob $batchJob): bool
    {
        $files = File::whereIn('id', $batchJob->file_ids)->get();
        
        foreach ($files as $index => $file) {
            try {
                // Extract basic metadata
                $metadata = [
                    'file_name' => $file->name,
                    'file_size' => $file->file_size,
                    'mime_type' => $file->mime_type,
                    'extension' => $file->extension,
                    'uploaded_at' => $file->created_at->format('Y-m-d H:i:s'),
                    'uploaded_by' => $file->uploaded_by,
                ];
                
                // Add file-specific metadata
                if ($file->mime_type === 'application/pdf') {
                    $metadata['pdf_metadata'] = $this->extractPdfMetadata($file);
                }
                
                // Store metadata (you could create a separate metadata table)
                $file->update(['metadata' => $metadata]);
                
                $this->updateBatchJobProgress($batchJob, $index + 1, true, "Metadata extracted for: {$file->name}");
                
            } catch (\Exception $e) {
                $this->updateBatchJobProgress($batchJob, $index + 1, false, "Metadata extraction error for {$file->name}: " . $e->getMessage());
            }
        }
        
        $this->completeBatchJob($batchJob);
        return true;
    }

    /**
     * Extract PDF metadata
     */
    private function extractPdfMetadata(File $file): array
    {
        try {
            $filePath = storage_path('app/' . $file->file_path);
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $details = $pdf->getDetails();
            
            return [
                'title' => $details->getTitle(),
                'author' => $details->getAuthor(),
                'subject' => $details->getSubject(),
                'creator' => $details->getCreator(),
                'producer' => $details->getProducer(),
                'creation_date' => $details->getCreationDate(),
                'modification_date' => $details->getModificationDate(),
                'page_count' => count($pdf->getPages()),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Update batch job progress
     */
    private function updateBatchJobProgress(BatchJob $batchJob, int $processedCount, bool $success, string $message = ''): void
    {
        $progress = ($processedCount / $batchJob->total_files) * 100;
        
        $updateData = [
            'processed_files' => $processedCount,
            'progress' => $progress,
        ];
        
        if ($success) {
            $updateData['successful_files'] = $batchJob->successful_files + 1;
        } else {
            $updateData['failed_files'] = $batchJob->failed_files + 1;
        }
        
        $batchJob->update($updateData);
        
        // Add progress log
        $batchJob->progressLogs()->create([
            'message' => $message,
            'progress' => $progress,
            'success' => $success,
            'processed_at' => now(),
        ]);
    }

    /**
     * Complete batch job
     */
    private function completeBatchJob(BatchJob $batchJob): void
    {
        $status = $batchJob->failed_files > 0 ? 'completed_with_errors' : 'completed';
        
        $batchJob->update([
            'status' => $status,
            'progress' => 100,
            'completed_at' => now(),
        ]);
        
        // Log activity
        $this->logBatchJobActivity($batchJob, 'completed');
    }

    /**
     * Get batch job status
     */
    public function getBatchJobStatus(BatchJob $batchJob): array
    {
        return [
            'id' => $batchJob->id,
            'operation_type' => $batchJob->operation_type,
            'operation_description' => self::OPERATION_TYPES[$batchJob->operation_type],
            'status' => $batchJob->status,
            'progress' => $batchJob->progress,
            'total_files' => $batchJob->total_files,
            'processed_files' => $batchJob->processed_files,
            'successful_files' => $batchJob->successful_files,
            'failed_files' => $batchJob->failed_files,
            'started_at' => $batchJob->started_at?->format('M j, Y g:i A'),
            'completed_at' => $batchJob->completed_at?->format('M j, Y g:i A'),
            'duration' => $batchJob->duration,
            'error_message' => $batchJob->error_message,
        ];
    }

    /**
     * Get batch job progress logs
     */
    public function getBatchJobLogs(BatchJob $batchJob, int $limit = 50): array
    {
        return $batchJob->progressLogs()
            ->orderBy('processed_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'message' => $log->message,
                    'progress' => $log->progress,
                    'success' => $log->success,
                    'processed_at' => $log->processed_at->format('M j, Y g:i:s'),
                ];
            })
            ->toArray();
    }

    /**
     * Get batch processing statistics
     */
    public function getBatchProcessingStats(): array
    {
        $totalJobs = BatchJob::count();
        $completedJobs = BatchJob::where('status', 'completed')->count();
        $completedWithErrors = BatchJob::where('status', 'completed_with_errors')->count();
        $failedJobs = BatchJob::where('status', 'failed')->count();
        $processingJobs = BatchJob::where('status', 'processing')->count();
        
        $totalFiles = BatchJob::sum('total_files');
        $successfulFiles = BatchJob::sum('successful_files');
        $failedFiles = BatchJob::sum('failed_files');
        
        return [
            'total_jobs' => $totalJobs,
            'completed_jobs' => $completedJobs,
            'completed_with_errors' => $completedWithErrors,
            'failed_jobs' => $failedJobs,
            'processing_jobs' => $processingJobs,
            'success_rate' => $totalJobs > 0 ? (($completedJobs + $completedWithErrors) / $totalJobs) * 100 : 0,
            'total_files_processed' => $totalFiles,
            'successful_files' => $successfulFiles,
            'failed_files' => $failedFiles,
            'file_success_rate' => $totalFiles > 0 ? ($successfulFiles / $totalFiles) * 100 : 0,
        ];
    }

    /**
     * Get batch job queue status
     */
    public function getQueueStatus(): array
    {
        return [
            'pending_jobs' => BatchJob::where('status', 'pending')->count(),
            'processing_jobs' => BatchJob::where('status', 'processing')->count(),
            'completed_today' => BatchJob::whereDate('created_at', today())->whereIn('status', ['completed', 'completed_with_errors'])->count(),
            'failed_today' => BatchJob::whereDate('created_at', today())->where('status', 'failed')->count(),
        ];
    }

    /**
     * Cancel a batch job
     */
    public function cancelBatchJob(BatchJob $batchJob): bool
    {
        if ($batchJob->status === 'processing') {
            $batchJob->update([
                'status' => 'cancelled',
                'completed_at' => now(),
            ]);
            
            $this->logBatchJobActivity($batchJob, 'cancelled');
            return true;
        }
        
        return false;
    }

    /**
     * Delete a batch job
     */
    public function deleteBatchJob(BatchJob $batchJob): bool
    {
        // Delete progress logs
        $batchJob->progressLogs()->delete();
        
        // Delete the batch job
        $batchJob->delete();
        
        return true;
    }

    /**
     * Log batch job activity
     */
    private function logBatchJobActivity(BatchJob $batchJob, string $action): void
    {
        $user = auth()->user();
        
        if ($user) {
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                "batch_job_{$action}",
                null,
                null,
                [
                    'batch_job_id' => $batchJob->id,
                    'operation_type' => $batchJob->operation_type,
                    'total_files' => $batchJob->total_files,
                    'status' => $batchJob->status,
                ]
            );
        }
    }

    /**
     * Get supported operation types
     */
    public function getSupportedOperationTypes(): array
    {
        return self::OPERATION_TYPES;
    }

    /**
     * Validate batch job options
     */
    public function validateBatchJobOptions(string $operationType, array $options): array
    {
        $errors = [];
        
        switch ($operationType) {
            case 'ocr':
                if (isset($options['language']) && !in_array($options['language'], ['eng', 'fra', 'deu', 'spa'])) {
                    $errors[] = 'Unsupported language for OCR';
                }
                break;
                
            case 'conversion':
                if (!isset($options['target_format'])) {
                    $errors[] = 'Target format is required for conversion';
                }
                break;
                
            case 'text_extraction':
                if (isset($options['extraction_type']) && !in_array($options['extraction_type'], ['full_text', 'structured_text', 'tables', 'forms', 'headings', 'lists', 'metadata', 'keywords', 'entities'])) {
                    $errors[] = 'Unsupported extraction type';
                }
                break;
        }
        
        return $errors;
    }
}
