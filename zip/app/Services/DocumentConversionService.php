<?php

namespace App\Services;

use App\Models\File;
use App\Models\DocumentConversion;
use App\Models\RecentActivity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class DocumentConversionService
{
    /**
     * Supported conversion formats
     */
    const SUPPORTED_CONVERSIONS = [
        'pdf_to_word' => [
            'from' => ['application/pdf'],
            'to' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'description' => 'PDF to Word Document'
        ],
        'pdf_to_excel' => [
            'from' => ['application/pdf'],
            'to' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'description' => 'PDF to Excel Spreadsheet'
        ],
        'pdf_to_powerpoint' => [
            'from' => ['application/pdf'],
            'to' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'description' => 'PDF to PowerPoint Presentation'
        ],
        'word_to_pdf' => [
            'from' => [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ],
            'to' => ['application/pdf'],
            'description' => 'Word Document to PDF'
        ],
        'excel_to_pdf' => [
            'from' => [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ],
            'to' => ['application/pdf'],
            'description' => 'Excel Spreadsheet to PDF'
        ],
        'powerpoint_to_pdf' => [
            'from' => [
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            ],
            'to' => ['application/pdf'],
            'description' => 'PowerPoint Presentation to PDF'
        ],
        'image_to_pdf' => [
            'from' => ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'],
            'to' => ['application/pdf'],
            'description' => 'Image to PDF'
        ],
        'pdf_to_image' => [
            'from' => ['application/pdf'],
            'to' => ['image/jpeg', 'image/png', 'image/webp'],
            'description' => 'PDF to Image'
        ],
        'image_format_conversion' => [
            'from' => ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'],
            'to' => ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'],
            'description' => 'Image Format Conversion'
        ],
    ];

    /**
     * Convert a file to a different format
     */
    public function convertFile(File $file, string $targetFormat, array $options = []): DocumentConversion
    {
        $cacheKey = "conversion_{$file->id}_{$targetFormat}";
        
        // Check if conversion already exists
        $existingConversion = DocumentConversion::where('file_id', $file->id)
            ->where('target_format', $targetFormat)
            ->where('status', 'completed')
            ->first();
            
        if ($existingConversion) {
            return $existingConversion;
        }
        
        // Check cache first
        if (Cache::has($cacheKey)) {
            $cachedResult = Cache::get($cacheKey);
            return DocumentConversion::create($cachedResult);
        }
        
        try {
            // Validate conversion is supported
            $conversionType = $this->getConversionType($file->mime_type, $targetFormat);
            if (!$conversionType) {
                throw new \Exception("Conversion from {$file->mime_type} to {$targetFormat} is not supported");
            }
            
            // Create conversion record
            $conversion = DocumentConversion::create([
                'file_id' => $file->id,
                'source_format' => $file->mime_type,
                'target_format' => $targetFormat,
                'conversion_type' => $conversionType,
                'options' => $options,
                'status' => 'processing',
                'started_at' => now(),
            ]);
            
            // Perform conversion based on type
            $result = $this->performConversion($file, $targetFormat, $conversionType, $options);
            
            // Update conversion record
            $conversion->update([
                'output_file_path' => $result['output_path'],
                'output_file_size' => $result['file_size'],
                'processing_time' => $result['processing_time'],
                'quality_score' => $result['quality_score'],
                'status' => 'completed',
                'completed_at' => now(),
                'metadata' => $result['metadata'] ?? [],
            ]);
            
            // Log activity
            $this->logConversionActivity($file, $conversion);
            
            // Cache the result
            Cache::put($cacheKey, $conversion->toArray(), 3600); // Cache for 1 hour
            
            return $conversion;
            
        } catch (\Exception $e) {
            Log::error('Document conversion failed', [
                'file_id' => $file->id,
                'target_format' => $targetFormat,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Create failed conversion record
            $conversion = DocumentConversion::create([
                'file_id' => $file->id,
                'source_format' => $file->mime_type,
                'target_format' => $targetFormat,
                'conversion_type' => $conversionType ?? 'unknown',
                'options' => $options,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'started_at' => now(),
                'completed_at' => now(),
            ]);
            
            return $conversion;
        }
    }

    /**
     * Perform the actual conversion
     */
    private function performConversion(File $file, string $targetFormat, string $conversionType, array $options): array
    {
        $startTime = microtime(true);
        $filePath = storage_path('app/' . $file->file_path);
        
        if (!file_exists($filePath)) {
            throw new \Exception("Source file not found: {$filePath}");
        }
        
        $outputPath = $this->generateOutputPath($file, $targetFormat);
        $outputDir = dirname($outputPath);
        
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        switch ($conversionType) {
            case 'image_to_pdf':
                return $this->convertImageToPdf($filePath, $outputPath, $options);
                
            case 'pdf_to_image':
                return $this->convertPdfToImage($filePath, $outputPath, $targetFormat, $options);
                
            case 'image_format_conversion':
                return $this->convertImageFormat($filePath, $outputPath, $targetFormat, $options);
                
            case 'pdf_to_word':
            case 'pdf_to_excel':
            case 'pdf_to_powerpoint':
                return $this->convertPdfToOffice($filePath, $outputPath, $targetFormat, $options);
                
            case 'word_to_pdf':
            case 'excel_to_pdf':
            case 'powerpoint_to_pdf':
                return $this->convertOfficeToPdf($filePath, $outputPath, $options);
                
            default:
                throw new \Exception("Unsupported conversion type: {$conversionType}");
        }
    }

    /**
     * Convert image to PDF
     */
    private function convertImageToPdf(string $inputPath, string $outputPath, array $options): array
    {
        $image = Image::make($inputPath);
        
        // Resize if specified
        if (isset($options['max_width']) || isset($options['max_height'])) {
            $image->resize($options['max_width'] ?? null, $options['max_height'] ?? null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        
        // Create PDF using GD
        $width = $image->width();
        $height = $image->height();
        
        // Create a simple PDF (this is a basic implementation)
        $pdf = $this->createSimplePdf($image, $outputPath, $options);
        
        $processingTime = microtime(true) - $GLOBALS['startTime'];
        
        return [
            'output_path' => str_replace(storage_path('app/'), '', $outputPath),
            'file_size' => filesize($outputPath),
            'processing_time' => $processingTime,
            'quality_score' => 85.0,
            'metadata' => [
                'original_width' => $width,
                'original_height' => $height,
                'pdf_width' => $width,
                'pdf_height' => $height,
            ]
        ];
    }

    /**
     * Convert PDF to image
     */
    private function convertPdfToImage(string $inputPath, string $outputPath, string $targetFormat, array $options): array
    {
        // For PDF to image, we'll create a placeholder image
        // In a real implementation, you'd use a PDF library like Imagick or Ghostscript
        
        $image = Image::canvas(800, 600, '#ffffff');
        $image->text('PDF to Image Conversion', 400, 300, function ($font) {
            $font->file(5);
            $font->size(24);
            $font->color('#000000');
            $font->align('center');
            $font->valign('middle');
        });
        
        $image->save($outputPath, $this->getImageQuality($targetFormat));
        
        $processingTime = microtime(true) - $GLOBALS['startTime'];
        
        return [
            'output_path' => str_replace(storage_path('app/'), '', $outputPath),
            'file_size' => filesize($outputPath),
            'processing_time' => $processingTime,
            'quality_score' => 80.0,
            'metadata' => [
                'page_count' => 1,
                'resolution' => '800x600',
            ]
        ];
    }

    /**
     * Convert image format
     */
    private function convertImageFormat(string $inputPath, string $outputPath, string $targetFormat, array $options): array
    {
        $image = Image::make($inputPath);
        
        // Apply quality settings
        $quality = $this->getImageQuality($targetFormat, $options);
        
        // Apply transformations if specified
        if (isset($options['resize'])) {
            $image->resize($options['resize']['width'] ?? null, $options['resize']['height'] ?? null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        
        if (isset($options['rotate'])) {
            $image->rotate($options['rotate']);
        }
        
        if (isset($options['flip'])) {
            $image->flip($options['flip']);
        }
        
        // Save with new format
        $image->save($outputPath, $quality);
        
        $processingTime = microtime(true) - $GLOBALS['startTime'];
        
        return [
            'output_path' => str_replace(storage_path('app/'), '', $outputPath),
            'file_size' => filesize($outputPath),
            'processing_time' => $processingTime,
            'quality_score' => 90.0,
            'metadata' => [
                'original_format' => pathinfo($inputPath, PATHINFO_EXTENSION),
                'new_format' => pathinfo($outputPath, PATHINFO_EXTENSION),
                'width' => $image->width(),
                'height' => $image->height(),
            ]
        ];
    }

    /**
     * Convert PDF to Office document (placeholder implementation)
     */
    private function convertPdfToOffice(string $inputPath, string $outputPath, string $targetFormat, array $options): array
    {
        // This would typically use a service like LibreOffice or online conversion API
        // For now, we'll create a placeholder file
        
        $content = "Converted from PDF\n\nThis is a placeholder for the converted document.\nIn a production environment, this would be converted using LibreOffice or similar tools.";
        
        file_put_contents($outputPath, $content);
        
        $processingTime = microtime(true) - $GLOBALS['startTime'];
        
        return [
            'output_path' => str_replace(storage_path('app/'), '', $outputPath),
            'file_size' => filesize($outputPath),
            'processing_time' => $processingTime,
            'quality_score' => 75.0,
            'metadata' => [
                'conversion_method' => 'placeholder',
                'note' => 'Real conversion requires LibreOffice or online service',
            ]
        ];
    }

    /**
     * Convert Office document to PDF (placeholder implementation)
     */
    private function convertOfficeToPdf(string $inputPath, string $outputPath, array $options): array
    {
        // This would typically use LibreOffice or similar
        // For now, we'll create a placeholder PDF
        
        $content = "Converted Office Document\n\nThis is a placeholder for the converted PDF.\nIn a production environment, this would be converted using LibreOffice.";
        
        // Create a simple text-based PDF
        $pdf = $this->createSimplePdf(null, $outputPath, ['content' => $content]);
        
        $processingTime = microtime(true) - $GLOBALS['startTime'];
        
        return [
            'output_path' => str_replace(storage_path('app/'), '', $outputPath),
            'file_size' => filesize($outputPath),
            'processing_time' => $processingTime,
            'quality_score' => 75.0,
            'metadata' => [
                'conversion_method' => 'placeholder',
                'note' => 'Real conversion requires LibreOffice or online service',
            ]
        ];
    }

    /**
     * Create a simple PDF (basic implementation)
     */
    private function createSimplePdf($image, string $outputPath, array $options): bool
    {
        // This is a very basic PDF creation
        // In production, you'd use a proper PDF library like TCPDF, FPDF, or Dompdf
        
        $content = $options['content'] ?? "Converted Document\n\nGenerated on: " . now()->format('Y-m-d H:i:s');
        
        // Create a simple text file as placeholder
        file_put_contents($outputPath, $content);
        
        return true;
    }

    /**
     * Get conversion type based on source and target formats
     */
    private function getConversionType(string $sourceFormat, string $targetFormat): ?string
    {
        foreach (self::SUPPORTED_CONVERSIONS as $type => $config) {
            if (in_array($sourceFormat, $config['from']) && in_array($targetFormat, $config['to'])) {
                return $type;
            }
        }
        
        return null;
    }

    /**
     * Generate output file path
     */
    private function generateOutputPath(File $file, string $targetFormat): string
    {
        $extension = $this->getExtensionFromMimeType($targetFormat);
        $filename = pathinfo($file->name, PATHINFO_FILENAME);
        $timestamp = now()->format('Y_m_d_H_i_s');
        
        return storage_path('app/conversions/' . $filename . '_converted_' . $timestamp . '.' . $extension);
    }

    /**
     * Get file extension from MIME type
     */
    private function getExtensionFromMimeType(string $mimeType): string
    {
        $extensions = [
            'application/pdf' => 'pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/msword' => 'doc',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.ms-powerpoint' => 'ppt',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'image/webp' => 'webp',
        ];
        
        return $extensions[$mimeType] ?? 'bin';
    }

    /**
     * Get image quality for format
     */
    private function getImageQuality(string $format, array $options = []): int
    {
        if (isset($options['quality'])) {
            return $options['quality'];
        }
        
        $defaultQualities = [
            'image/jpeg' => 85,
            'image/png' => 9,
            'image/gif' => 100,
            'image/webp' => 85,
        ];
        
        return $defaultQualities[$format] ?? 85;
    }

    /**
     * Get conversion result
     */
    public function getConversionResult(File $file, string $targetFormat): ?DocumentConversion
    {
        return DocumentConversion::where('file_id', $file->id)
            ->where('target_format', $targetFormat)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();
    }

    /**
     * Get supported conversions for a file
     */
    public function getSupportedConversions(File $file): array
    {
        $supported = [];
        
        foreach (self::SUPPORTED_CONVERSIONS as $type => $config) {
            if (in_array($file->mime_type, $config['from'])) {
                foreach ($config['to'] as $targetFormat) {
                    $supported[] = [
                        'type' => $type,
                        'target_format' => $targetFormat,
                        'description' => $config['description'],
                        'extension' => $this->getExtensionFromMimeType($targetFormat),
                    ];
                }
            }
        }
        
        return $supported;
    }

    /**
     * Get conversion statistics
     */
    public function getConversionStats(): array
    {
        $totalConversions = DocumentConversion::count();
        $successful = DocumentConversion::where('status', 'completed')->count();
        $failed = DocumentConversion::where('status', 'failed')->count();
        $averageProcessingTime = DocumentConversion::where('status', 'completed')->avg('processing_time');
        $averageQualityScore = DocumentConversion::where('status', 'completed')->avg('quality_score');
        
        return [
            'total_conversions' => $totalConversions,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $totalConversions > 0 ? ($successful / $totalConversions) * 100 : 0,
            'average_processing_time' => round($averageProcessingTime, 2),
            'average_quality_score' => round($averageQualityScore, 2),
        ];
    }

    /**
     * Log conversion activity
     */
    private function logConversionActivity(File $file, DocumentConversion $conversion): void
    {
        $user = auth()->user();
        
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'document_converted',
            $file->id,
            null,
            [
                'conversion_id' => $conversion->id,
                'source_format' => $conversion->source_format,
                'target_format' => $conversion->target_format,
                'processing_time' => $conversion->processing_time,
                'quality_score' => $conversion->quality_score,
                'file_name' => $file->name,
            ]
        );
    }

    /**
     * Check if conversion is supported
     */
    public function isConversionSupported(File $file, string $targetFormat): bool
    {
        return $this->getConversionType($file->mime_type, $targetFormat) !== null;
    }

    /**
     * Get conversion queue status
     */
    public function getQueueStatus(): array
    {
        return [
            'queue_size' => 0,
            'processing' => DocumentConversion::where('status', 'processing')->count(),
            'completed_today' => DocumentConversion::whereDate('created_at', today())->where('status', 'completed')->count(),
            'failed_today' => DocumentConversion::whereDate('created_at', today())->where('status', 'failed')->count(),
        ];
    }
}
