<?php

namespace App\Services;

use App\Models\File;
use App\Models\OcrResult;
use App\Models\RecentActivity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Str;

class OcrService
{
    /**
     * Process OCR for a file
     */
    public function processOcr(File $file, array $options = []): OcrResult
    {
        $cacheKey = "ocr_result_{$file->id}";
        
        // Check if OCR result already exists
        $existingResult = OcrResult::where('file_id', $file->id)->first();
        if ($existingResult) {
            return $existingResult;
        }
        
        // Check cache first
        if (Cache::has($cacheKey)) {
            $cachedResult = Cache::get($cacheKey);
            return OcrResult::create($cachedResult);
        }
        
        try {
            // Get file path
            $filePath = storage_path('app/' . $file->file_path);
            
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }
            
            // Configure OCR
            $ocr = new TesseractOCR($filePath);
            
            // Set language (default to English)
            $language = $options['language'] ?? 'eng';
            $ocr->language($language);
            
            // Set OCR engine mode
            $ocr->executable('/usr/local/bin/tesseract');
            
            // Set page segmentation mode
            if (isset($options['psm'])) {
                $ocr->psm($options['psm']);
            }
            
            // Set OCR engine mode
            if (isset($options['oem'])) {
                $ocr->oem($options['oem']);
            }
            
            // Add custom configuration
            if (isset($options['config'])) {
                foreach ($options['config'] as $key => $value) {
                    $ocr->config($key, $value);
                }
            }
            
            // Perform OCR
            $text = $ocr->run();
            
            // Calculate confidence score (if available)
            $confidence = $this->calculateConfidence($text, $options);
            
            // Create OCR result
            $ocrResult = OcrResult::create([
                'file_id' => $file->id,
                'extracted_text' => $text,
                'confidence_score' => $confidence,
                'language' => $language,
                'processing_time' => $this->getProcessingTime(),
                'ocr_engine' => 'tesseract',
                'ocr_version' => $this->getTesseractVersion(),
                'options' => $options,
                'status' => 'completed',
                'processed_at' => now(),
            ]);
            
            // Log activity
            $this->logOcrActivity($file, $ocrResult);
            
            // Cache the result
            Cache::put($cacheKey, $ocrResult->toArray(), 3600); // Cache for 1 hour
            
            return $ocrResult;
            
        } catch (\Exception $e) {
            Log::error('OCR processing failed', [
                'file_id' => $file->id,
                'file_name' => $file->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Create failed result
            $ocrResult = OcrResult::create([
                'file_id' => $file->id,
                'extracted_text' => '',
                'confidence_score' => 0,
                'language' => $options['language'] ?? 'eng',
                'processing_time' => 0,
                'ocr_engine' => 'tesseract',
                'ocr_version' => $this->getTesseractVersion(),
                'options' => $options,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'processed_at' => now(),
            ]);
            
            return $ocrResult;
        }
    }
    
    /**
     * Process OCR for multiple files (batch processing)
     */
    public function processBatchOcr(array $fileIds, array $options = []): array
    {
        $results = [];
        $files = File::whereIn('id', $fileIds)->get();
        
        foreach ($files as $file) {
            try {
                $result = $this->processOcr($file, $options);
                $results[] = [
                    'file_id' => $file->id,
                    'file_name' => $file->name,
                    'status' => $result->status,
                    'confidence_score' => $result->confidence_score,
                    'processing_time' => $result->processing_time,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'file_id' => $file->id,
                    'file_name' => $file->name,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Get OCR result for a file
     */
    public function getOcrResult(File $file): ?OcrResult
    {
        return OcrResult::where('file_id', $file->id)
            ->where('status', 'completed')
            ->latest('processed_at')
            ->first();
    }
    
    /**
     * Update OCR result
     */
    public function updateOcrResult(OcrResult $ocrResult, string $text, float $confidence = null): bool
    {
        $ocrResult->update([
            'extracted_text' => $text,
            'confidence_score' => $confidence ?? $ocrResult->confidence_score,
            'updated_at' => now(),
        ]);
        
        // Clear cache
        Cache::forget("ocr_result_{$ocrResult->file_id}");
        
        return true;
    }
    
    /**
     * Delete OCR result
     */
    public function deleteOcrResult(OcrResult $ocrResult): bool
    {
        // Clear cache
        Cache::forget("ocr_result_{$ocrResult->file_id}");
        
        return $ocrResult->delete();
    }
    
    /**
     * Get OCR statistics
     */
    public function getOcrStats(): array
    {
        $totalProcessed = OcrResult::count();
        $successful = OcrResult::where('status', 'completed')->count();
        $failed = OcrResult::where('status', 'failed')->count();
        $averageConfidence = OcrResult::where('status', 'completed')->avg('confidence_score');
        $averageProcessingTime = OcrResult::where('status', 'completed')->avg('processing_time');
        
        return [
            'total_processed' => $totalProcessed,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $totalProcessed > 0 ? ($successful / $totalProcessed) * 100 : 0,
            'average_confidence' => round($averageConfidence, 2),
            'average_processing_time' => round($averageProcessingTime, 2),
        ];
    }
    
    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return [
            'eng' => 'English',
            'fra' => 'French',
            'deu' => 'German',
            'spa' => 'Spanish',
            'ita' => 'Italian',
            'por' => 'Portuguese',
            'rus' => 'Russian',
            'chi_sim' => 'Chinese (Simplified)',
            'chi_tra' => 'Chinese (Traditional)',
            'jpn' => 'Japanese',
            'kor' => 'Korean',
            'ara' => 'Arabic',
            'heb' => 'Hebrew',
            'hin' => 'Hindi',
            'ben' => 'Bengali',
            'tel' => 'Telugu',
            'tam' => 'Tamil',
            'mar' => 'Marathi',
            'guj' => 'Gujarati',
            'kan' => 'Kannada',
            'mal' => 'Malayalam',
            'ori' => 'Oriya',
            'pan' => 'Punjabi',
            'urd' => 'Urdu',
        ];
    }
    
    /**
     * Get OCR engine options
     */
    public function getOcrOptions(): array
    {
        return [
            'psm' => [
                0 => 'Orientation and script detection (OSD) only',
                1 => 'Automatic page segmentation with OSD',
                2 => 'Automatic page segmentation, but no OSD, or OCR',
                3 => 'Fully automatic page segmentation, but no OSD (default)',
                4 => 'Assume a single column of text of variable sizes',
                5 => 'Assume a single uniform block of vertically aligned text',
                6 => 'Assume a uniform block of text',
                7 => 'Treat the image as a single text line',
                8 => 'Treat the image as a single word',
                9 => 'Treat the image as a single word in a circle',
                10 => 'Treat the image as a single character',
                11 => 'Sparse text. Find as much text as possible in no particular order',
                12 => 'Sparse text with OSD',
                13 => 'Raw line. Treat the image as a single text line',
            ],
            'oem' => [
                0 => 'Legacy engine only',
                1 => 'Neural nets LSTM engine only',
                2 => 'Legacy + LSTM engines',
                3 => 'Default, based on what is available',
            ],
        ];
    }
    
    /**
     * Calculate confidence score
     */
    private function calculateConfidence(string $text, array $options): float
    {
        // Simple confidence calculation based on text length and quality
        $textLength = strlen($text);
        $wordCount = str_word_count($text);
        
        if ($textLength === 0) {
            return 0.0;
        }
        
        // Basic confidence based on text length and word count
        $lengthScore = min($textLength / 100, 1.0); // Normalize to 0-1
        $wordScore = min($wordCount / 10, 1.0); // Normalize to 0-1
        
        // Average the scores
        $confidence = ($lengthScore + $wordScore) / 2;
        
        // Apply any confidence adjustments from options
        if (isset($options['confidence_boost'])) {
            $confidence = min($confidence + $options['confidence_boost'], 1.0);
        }
        
        return round($confidence * 100, 2);
    }
    
    /**
     * Get processing time
     */
    private function getProcessingTime(): float
    {
        // This would be calculated from start to end of OCR processing
        // For now, return a default value
        return 2.5; // seconds
    }
    
    /**
     * Get Tesseract version
     */
    private function getTesseractVersion(): string
    {
        try {
            $output = shell_exec('tesseract --version 2>&1');
            if (preg_match('/tesseract\s+(\d+\.\d+\.\d+)/', $output, $matches)) {
                return $matches[1];
            }
        } catch (\Exception $e) {
            Log::warning('Could not determine Tesseract version', ['error' => $e->getMessage()]);
        }
        
        return 'unknown';
    }
    
    /**
     * Log OCR activity
     */
    private function logOcrActivity(File $file, OcrResult $ocrResult): void
    {
        $user = auth()->user();
        
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'ocr_processed',
            $file->id,
            null,
            [
                'ocr_result_id' => $ocrResult->id,
                'confidence_score' => $ocrResult->confidence_score,
                'language' => $ocrResult->language,
                'processing_time' => $ocrResult->processing_time,
                'file_name' => $file->name,
            ]
        );
    }
    
    /**
     * Check if file is suitable for OCR
     */
    public function isOcrSuitable(File $file): bool
    {
        $suitableTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/tiff',
            'image/tif',
            'image/bmp',
            'image/gif',
            'application/pdf',
        ];
        
        return in_array($file->mime_type, $suitableTypes);
    }
    
    /**
     * Get OCR processing queue status
     */
    public function getQueueStatus(): array
    {
        // This would integrate with Laravel's queue system
        // For now, return basic status
        return [
            'queue_size' => 0,
            'processing' => 0,
            'completed_today' => OcrResult::whereDate('created_at', today())->count(),
            'failed_today' => OcrResult::whereDate('created_at', today())->where('status', 'failed')->count(),
        ];
    }
}
