<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Smalot\PdfParser\Parser;
use Spatie\PdfToImage\Pdf;

class DocumentPreviewService
{
    /**
     * Supported preview formats
     */
    private $supportedFormats = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'image',
        'image/png' => 'image',
        'image/gif' => 'image',
        'image/bmp' => 'image',
        'image/tiff' => 'image',
        'image/webp' => 'image',
        'text/plain' => 'text',
        'text/html' => 'text',
        'text/css' => 'text',
        'text/javascript' => 'text',
        'application/json' => 'text',
        'application/xml' => 'text',
        'application/msword' => 'office',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'office',
        'application/vnd.ms-excel' => 'office',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'office',
        'application/vnd.ms-powerpoint' => 'office',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'office',
    ];

    /**
     * Generate preview for a file
     */
    public function generatePreview(File $file): array
    {
        try {
            $path = Storage::disk('local')->path($file->file_path);
            $previewType = $this->getPreviewType($file->mime_type);
            
            switch ($previewType) {
                case 'pdf':
                    return $this->generatePdfPreview($file, $path);
                    
                case 'image':
                    return $this->generateImagePreview($file, $path);
                    
                case 'text':
                    return $this->generateTextPreview($file, $path);
                    
                case 'office':
                    return $this->generateOfficePreview($file, $path);
                    
                default:
                    return $this->generateGenericPreview($file);
            }
        } catch (\Exception $e) {
            Log::error("Error generating preview for file {$file->id}: " . $e->getMessage());
            return $this->generateErrorPreview($file, $e->getMessage());
        }
    }

    /**
     * Generate PDF preview
     */
    private function generatePdfPreview(File $file, string $path): array
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
            
            // Get PDF info
            $parser = new Parser();
            $pdfDoc = $parser->parseFile($path);
            $pages = $pdfDoc->getPages();
            
            return [
                'type' => 'pdf',
                'preview_url' => Storage::disk('local')->url($previewPath),
                'preview_path' => $previewPath,
                'page_count' => count($pages),
                'text_content' => $pdfDoc->getText(),
                'metadata' => [
                    'title' => $pdfDoc->getDetails()->get('Title'),
                    'author' => $pdfDoc->getDetails()->get('Author'),
                    'creator' => $pdfDoc->getDetails()->get('Creator'),
                    'producer' => $pdfDoc->getDetails()->get('Producer'),
                    'creation_date' => $pdfDoc->getDetails()->get('CreationDate'),
                    'modification_date' => $pdfDoc->getDetails()->get('ModDate'),
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error generating PDF preview: " . $e->getMessage());
            return $this->generateErrorPreview($file, "PDF preview generation failed");
        }
    }

    /**
     * Generate image preview
     */
    private function generateImagePreview(File $file, string $path): array
    {
        try {
            $previewPath = 'previews/' . $file->id . '_preview.jpg';
            $thumbnailPath = 'previews/' . $file->id . '_thumbnail.jpg';
            $fullPreviewPath = Storage::disk('local')->path($previewPath);
            $fullThumbnailPath = Storage::disk('local')->path($thumbnailPath);
            
            // Create previews directory if it doesn't exist
            if (!file_exists(dirname($fullPreviewPath))) {
                mkdir(dirname($fullPreviewPath), 0755, true);
            }
            
            // Create preview (medium size)
            $image = Image::make($path);
            $image->resize(800, 800, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $image->save($fullPreviewPath, 80);
            
            // Create thumbnail (small size)
            $thumbnail = Image::make($path);
            $thumbnail->resize(200, 200, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumbnail->save($fullThumbnailPath, 80);
            
            return [
                'type' => 'image',
                'preview_url' => Storage::disk('local')->url($previewPath),
                'thumbnail_url' => Storage::disk('local')->url($thumbnailPath),
                'preview_path' => $previewPath,
                'thumbnail_path' => $thumbnailPath,
                'dimensions' => [
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'original_width' => $image->getWidth(),
                    'original_height' => $image->getHeight(),
                ],
                'metadata' => [
                    'format' => $image->mime(),
                    'size' => $file->file_size,
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error generating image preview: " . $e->getMessage());
            return $this->generateErrorPreview($file, "Image preview generation failed");
        }
    }

    /**
     * Generate text preview
     */
    private function generateTextPreview(File $file, string $path): array
    {
        try {
            $content = file_get_contents($path);
            $previewContent = substr($content, 0, 5000); // First 5000 characters
            $hasMore = strlen($content) > 5000;
            
            return [
                'type' => 'text',
                'content' => $previewContent,
                'full_content' => $content,
                'has_more' => $hasMore,
                'total_length' => strlen($content),
                'preview_length' => 5000,
                'metadata' => [
                    'encoding' => mb_detect_encoding($content),
                    'line_count' => substr_count($content, "\n") + 1,
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error generating text preview: " . $e->getMessage());
            return $this->generateErrorPreview($file, "Text preview generation failed");
        }
    }

    /**
     * Generate Office document preview
     */
    private function generateOfficePreview(File $file, string $path): array
    {
        try {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            
            switch ($extension) {
                case 'docx':
                    return $this->generateDocxPreview($file, $path);
                    
                case 'xlsx':
                    return $this->generateXlsxPreview($file, $path);
                    
                case 'pptx':
                    return $this->generatePptxPreview($file, $path);
                    
                default:
                    return $this->generateGenericPreview($file);
            }
        } catch (\Exception $e) {
            Log::error("Error generating Office preview: " . $e->getMessage());
            return $this->generateErrorPreview($file, "Office document preview generation failed");
        }
    }

    /**
     * Generate DOCX preview
     */
    private function generateDocxPreview(File $file, string $path): array
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($path) === true) {
                $content = $zip->getFromName('word/document.xml');
                $zip->close();
                
                if ($content) {
                    // Extract text content
                    $text = strip_tags($content);
                    $previewText = substr($text, 0, 1000);
                    
                    return [
                        'type' => 'office',
                        'office_type' => 'word',
                        'content' => $previewText,
                        'full_content' => $text,
                        'has_more' => strlen($text) > 1000,
                        'total_length' => strlen($text),
                        'preview_length' => 1000,
                        'metadata' => [
                            'document_type' => 'Word Document',
                            'extension' => 'docx',
                        ]
                    ];
                }
            }
            
            return $this->generateGenericPreview($file);
        } catch (\Exception $e) {
            Log::error("Error generating DOCX preview: " . $e->getMessage());
            return $this->generateErrorPreview($file, "DOCX preview generation failed");
        }
    }

    /**
     * Generate XLSX preview
     */
    private function generateXlsxPreview(File $file, string $path): array
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($path) === true) {
                $content = $zip->getFromName('xl/sharedStrings.xml');
                $zip->close();
                
                if ($content) {
                    $text = strip_tags($content);
                    $previewText = substr($text, 0, 1000);
                    
                    return [
                        'type' => 'office',
                        'office_type' => 'excel',
                        'content' => $previewText,
                        'full_content' => $text,
                        'has_more' => strlen($text) > 1000,
                        'total_length' => strlen($text),
                        'preview_length' => 1000,
                        'metadata' => [
                            'document_type' => 'Excel Spreadsheet',
                            'extension' => 'xlsx',
                        ]
                    ];
                }
            }
            
            return $this->generateGenericPreview($file);
        } catch (\Exception $e) {
            Log::error("Error generating XLSX preview: " . $e->getMessage());
            return $this->generateErrorPreview($file, "XLSX preview generation failed");
        }
    }

    /**
     * Generate PPTX preview
     */
    private function generatePptxPreview(File $file, string $path): array
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($path) === true) {
                $content = $zip->getFromName('ppt/slides/slide1.xml');
                $zip->close();
                
                if ($content) {
                    $text = strip_tags($content);
                    $previewText = substr($text, 0, 1000);
                    
                    return [
                        'type' => 'office',
                        'office_type' => 'powerpoint',
                        'content' => $previewText,
                        'full_content' => $text,
                        'has_more' => strlen($text) > 1000,
                        'total_length' => strlen($text),
                        'preview_length' => 1000,
                        'metadata' => [
                            'document_type' => 'PowerPoint Presentation',
                            'extension' => 'pptx',
                        ]
                    ];
                }
            }
            
            return $this->generateGenericPreview($file);
        } catch (\Exception $e) {
            Log::error("Error generating PPTX preview: " . $e->getMessage());
            return $this->generateErrorPreview($file, "PPTX preview generation failed");
        }
    }

    /**
     * Generate generic preview for unsupported formats
     */
    private function generateGenericPreview(File $file): array
    {
        return [
            'type' => 'generic',
            'message' => 'Preview not available for this file type',
            'file_info' => [
                'name' => $file->name,
                'size' => $file->human_size,
                'type' => $file->mime_type,
                'extension' => $file->extension,
            ],
            'metadata' => [
                'preview_supported' => false,
                'download_available' => true,
            ]
        ];
    }

    /**
     * Generate error preview
     */
    private function generateErrorPreview(File $file, string $error): array
    {
        return [
            'type' => 'error',
            'error' => $error,
            'file_info' => [
                'name' => $file->name,
                'size' => $file->human_size,
                'type' => $file->mime_type,
            ],
            'metadata' => [
                'preview_supported' => false,
                'error_occurred' => true,
            ]
        ];
    }

    /**
     * Get preview type for MIME type
     */
    private function getPreviewType(string $mimeType): string
    {
        return $this->supportedFormats[$mimeType] ?? 'generic';
    }

    /**
     * Check if file supports preview
     */
    public function supportsPreview(File $file): bool
    {
        return isset($this->supportedFormats[$file->mime_type]);
    }

    /**
     * Get supported MIME types
     */
    public function getSupportedMimeTypes(): array
    {
        return array_keys($this->supportedFormats);
    }

    /**
     * Clean up preview files
     */
    public function cleanupPreview(File $file): bool
    {
        try {
            if ($file->search_metadata && isset($file->search_metadata['preview_path'])) {
                Storage::disk('local')->delete($file->search_metadata['preview_path']);
            }
            
            if ($file->search_metadata && isset($file->search_metadata['thumbnail_path'])) {
                Storage::disk('local')->delete($file->search_metadata['thumbnail_path']);
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error cleaning up preview for file {$file->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Batch generate previews
     */
    public function batchGeneratePreviews(array $fileIds): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($fileIds as $fileId) {
            try {
                $file = File::find($fileId);
                if ($file && $this->supportsPreview($file)) {
                    $preview = $this->generatePreview($file);
                    
                    // Update file with preview metadata
                    $searchMetadata = $file->search_metadata ?? [];
                    $searchMetadata['preview'] = $preview;
                    
                    $file->update([
                        'search_metadata' => $searchMetadata,
                        'indexed_at' => now(),
                    ]);
                    
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "File {$fileId} not found or preview not supported";
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Error processing file {$fileId}: " . $e->getMessage();
            }
        }
        
        return $results;
    }
} 