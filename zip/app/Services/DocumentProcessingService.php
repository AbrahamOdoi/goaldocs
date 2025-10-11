<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\PdfToImage\Pdf;
use Intervention\Image\Facades\Image;
use Smalot\PdfParser\Parser;

class DocumentProcessingService
{
    /**
     * Extract text from various document types
     */
    public function extractText(File $file): ?string
    {
        try {
            $path = Storage::disk('local')->path($file->file_path);
            
            switch ($file->mime_type) {
                case 'application/pdf':
                    return $this->extractTextFromPdf($path);
                    
                case 'text/plain':
                    return $this->extractTextFromPlainText($path);
                    
                case 'application/msword':
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                    return $this->extractTextFromWord($path);
                    
                case 'application/vnd.ms-excel':
                case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                    return $this->extractTextFromExcel($path);
                    
                case 'image/jpeg':
                case 'image/png':
                case 'image/gif':
                case 'image/bmp':
                case 'image/tiff':
                    return $this->extractTextFromImage($path);
                    
                default:
                    Log::info("Text extraction not supported for mime type: {$file->mime_type}");
                    return null;
            }
        } catch (\Exception $e) {
            Log::error("Error extracting text from file {$file->id}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extract text from PDF files
     */
    private function extractTextFromPdf(string $path): ?string
    {
        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            
            return $text ?: null;
        } catch (\Exception $e) {
            Log::error("Error extracting text from PDF: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extract text from plain text files
     */
    private function extractTextFromPlainText(string $path): ?string
    {
        try {
            $content = file_get_contents($path);
            return $content ?: null;
        } catch (\Exception $e) {
            Log::error("Error reading plain text file: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extract text from Word documents
     */
    private function extractTextFromWord(string $path): ?string
    {
        try {
            // For .docx files, we can extract text from the XML content
            if (pathinfo($path, PATHINFO_EXTENSION) === 'docx') {
                $zip = new \ZipArchive();
                if ($zip->open($path) === true) {
                    $content = $zip->getFromName('word/document.xml');
                    $zip->close();
                    
                    if ($content) {
                        // Remove XML tags and extract text
                        $text = strip_tags($content);
                        return $text ?: null;
                    }
                }
            }
            
            // For .doc files, we might need additional libraries
            Log::info("Word document text extraction not fully implemented");
            return null;
        } catch (\Exception $e) {
            Log::error("Error extracting text from Word document: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extract text from Excel files
     */
    private function extractTextFromExcel(string $path): ?string
    {
        try {
            // For .xlsx files, extract text from cells
            if (pathinfo($path, PATHINFO_EXTENSION) === 'xlsx') {
                $zip = new \ZipArchive();
                if ($zip->open($path) === true) {
                    $content = $zip->getFromName('xl/sharedStrings.xml');
                    $zip->close();
                    
                    if ($content) {
                        $text = strip_tags($content);
                        return $text ?: null;
                    }
                }
            }
            
            Log::info("Excel document text extraction not fully implemented");
            return null;
        } catch (\Exception $e) {
            Log::error("Error extracting text from Excel document: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extract text from images using OCR
     */
    private function extractTextFromImage(string $path): ?string
    {
        try {
            // For now, return null as OCR requires additional setup
            // In production, you would integrate with services like:
            // - Google Cloud Vision API
            // - AWS Textract
            // - Tesseract OCR
            // - Azure Computer Vision
            
            Log::info("OCR text extraction not implemented - requires OCR service integration");
            return null;
        } catch (\Exception $e) {
            Log::error("Error extracting text from image: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate document preview
     */
    public function generatePreview(File $file): ?string
    {
        try {
            $path = Storage::disk('local')->path($file->file_path);
            
            switch ($file->mime_type) {
                case 'application/pdf':
                    return $this->generatePdfPreview($file, $path);
                    
                case 'image/jpeg':
                case 'image/png':
                case 'image/gif':
                case 'image/bmp':
                    return $this->generateImagePreview($file, $path);
                    
                default:
                    return null;
            }
        } catch (\Exception $e) {
            Log::error("Error generating preview for file {$file->id}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate PDF preview (first page as image)
     */
    private function generatePdfPreview(File $file, string $path): ?string
    {
        try {
            $previewPath = 'previews/' . $file->id . '_preview.jpg';
            $fullPreviewPath = Storage::disk('local')->path($previewPath);
            
            // Create previews directory if it doesn't exist
            if (!file_exists(dirname($fullPreviewPath))) {
                mkdir(dirname($fullPreviewPath), 0755, true);
            }
            
            // Convert first page to image
            $pdf = new Pdf($path);
            $pdf->setPage(1)
                ->setOutputFormat('jpg')
                ->setResolution(150)
                ->saveImage($fullPreviewPath);
            
            return $previewPath;
        } catch (\Exception $e) {
            Log::error("Error generating PDF preview: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate image preview (resized thumbnail)
     */
    private function generateImagePreview(File $file, string $path): ?string
    {
        try {
            $previewPath = 'previews/' . $file->id . '_preview.jpg';
            $fullPreviewPath = Storage::disk('local')->path($previewPath);
            
            // Create previews directory if it doesn't exist
            if (!file_exists(dirname($fullPreviewPath))) {
                mkdir(dirname($fullPreviewPath), 0755, true);
            }
            
            // Create thumbnail
            $image = Image::make($path);
            $image->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $image->save($fullPreviewPath, 80);
            
            return $previewPath;
        } catch (\Exception $e) {
            Log::error("Error generating image preview: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Process and index a file for search
     */
    public function processFileForSearch(File $file): bool
    {
        try {
            // Extract text content
            $extractedText = $this->extractText($file);
            
            // Generate preview
            $previewPath = $this->generatePreview($file);
            
            // Update file with extracted data
            $file->update([
                'extracted_text' => $extractedText,
                'search_metadata' => [
                    'preview_path' => $previewPath,
                    'text_length' => $extractedText ? strlen($extractedText) : 0,
                    'processed_at' => now()->toISOString(),
                ],
                'indexed_at' => now(),
            ]);
            
            Log::info("File {$file->id} processed for search successfully");
            return true;
        } catch (\Exception $e) {
            Log::error("Error processing file {$file->id} for search: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Batch process files for search indexing
     */
    public function batchProcessFiles(array $fileIds): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($fileIds as $fileId) {
            try {
                $file = File::find($fileId);
                if ($file && $this->processFileForSearch($file)) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to process file {$fileId}";
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error processing file {$fileId}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Get supported file types for text extraction
     */
    public function getSupportedTextExtractionTypes(): array
    {
        return [
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
    }
    
    /**
     * Get supported file types for preview generation
     */
    public function getSupportedPreviewTypes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/tiff',
        ];
    }
} 