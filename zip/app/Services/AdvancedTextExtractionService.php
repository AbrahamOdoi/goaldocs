<?php

namespace App\Services;

use App\Models\File;
use App\Models\TextExtraction;
use App\Models\RecentActivity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;

class AdvancedTextExtractionService
{
    /**
     * Supported extraction types
     */
    const EXTRACTION_TYPES = [
        'full_text' => 'Full Text Extraction',
        'structured_text' => 'Structured Text with Layout',
        'tables' => 'Table Extraction',
        'forms' => 'Form Field Extraction',
        'headings' => 'Heading Structure',
        'lists' => 'List Extraction',
        'metadata' => 'Document Metadata',
        'keywords' => 'Keyword Extraction',
        'entities' => 'Named Entity Recognition',
    ];

    /**
     * Extract text from a file with advanced options
     */
    public function extractText(File $file, array $options = []): TextExtraction
    {
        $cacheKey = "text_extraction_{$file->id}_" . md5(serialize($options));
        
        // Check if extraction already exists
        $existingExtraction = TextExtraction::where('file_id', $file->id)
            ->where('extraction_type', $options['type'] ?? 'full_text')
            ->first();
            
        if ($existingExtraction) {
            return $existingExtraction;
        }
        
        // Check cache first
        if (Cache::has($cacheKey)) {
            $cachedResult = Cache::get($cacheKey);
            return TextExtraction::create($cachedResult);
        }
        
        try {
            $extractionType = $options['type'] ?? 'full_text';
            
            // Create extraction record
            $extraction = TextExtraction::create([
                'file_id' => $file->id,
                'extraction_type' => $extractionType,
                'options' => $options,
                'status' => 'processing',
                'started_at' => now(),
            ]);
            
            // Perform extraction based on file type
            $result = $this->performExtraction($file, $extractionType, $options);
            
            // Update extraction record
            $extraction->update([
                'extracted_text' => $result['text'],
                'structured_data' => $result['structured_data'] ?? [],
                'metadata' => $result['metadata'] ?? [],
                'processing_time' => $result['processing_time'],
                'quality_score' => $result['quality_score'],
                'word_count' => $result['word_count'],
                'character_count' => $result['character_count'],
                'page_count' => $result['page_count'] ?? 1,
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            
            // Log activity
            $this->logExtractionActivity($file, $extraction);
            
            // Cache the result
            Cache::put($cacheKey, $extraction->toArray(), 3600); // Cache for 1 hour
            
            return $extraction;
            
        } catch (\Exception $e) {
            Log::error('Text extraction failed', [
                'file_id' => $file->id,
                'extraction_type' => $options['type'] ?? 'full_text',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Create failed extraction record
            $extraction = TextExtraction::create([
                'file_id' => $file->id,
                'extraction_type' => $options['type'] ?? 'full_text',
                'options' => $options,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'started_at' => now(),
                'completed_at' => now(),
            ]);
            
            return $extraction;
        }
    }

    /**
     * Perform the actual text extraction
     */
    private function performExtraction(File $file, string $extractionType, array $options): array
    {
        $startTime = microtime(true);
        $filePath = storage_path('app/' . $file->file_path);
        
        if (!file_exists($filePath)) {
            throw new \Exception("Source file not found: {$filePath}");
        }
        
        switch ($file->mime_type) {
            case 'application/pdf':
                return $this->extractFromPdf($filePath, $extractionType, $options);
                
            case 'text/plain':
            case 'text/html':
            case 'text/css':
            case 'text/javascript':
            case 'application/javascript':
            case 'application/json':
            case 'application/xml':
            case 'text/xml':
            case 'text/csv':
            case 'text/markdown':
                return $this->extractFromText($filePath, $extractionType, $options);
                
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/png':
            case 'image/gif':
            case 'image/bmp':
            case 'image/webp':
                return $this->extractFromImage($filePath, $extractionType, $options);
                
            default:
                throw new \Exception("Unsupported file type for text extraction: {$file->mime_type}");
        }
    }

    /**
     * Extract text from PDF with advanced features
     */
    private function extractFromPdf(string $filePath, string $extractionType, array $options): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        
        $pages = $pdf->getPages();
        $pageCount = count($pages);
        
        $extractedText = '';
        $structuredData = [];
        $metadata = [];
        
        foreach ($pages as $pageIndex => $page) {
            $pageNumber = $pageIndex + 1;
            $pageText = $page->getText();
            
            switch ($extractionType) {
                case 'full_text':
                    $extractedText .= "=== Page {$pageNumber} ===\n{$pageText}\n\n";
                    break;
                    
                case 'structured_text':
                    $structuredData[] = [
                        'page' => $pageNumber,
                        'text' => $pageText,
                        'layout' => $this->analyzeLayout($page),
                    ];
                    $extractedText .= $pageText . "\n\n";
                    break;
                    
                case 'tables':
                    $tables = $this->extractTables($pageText);
                    $structuredData[] = [
                        'page' => $pageNumber,
                        'tables' => $tables,
                    ];
                    break;
                    
                case 'forms':
                    $forms = $this->extractFormFields($pageText);
                    $structuredData[] = [
                        'page' => $pageNumber,
                        'forms' => $forms,
                    ];
                    break;
                    
                case 'headings':
                    $headings = $this->extractHeadings($pageText);
                    $structuredData[] = [
                        'page' => $pageNumber,
                        'headings' => $headings,
                    ];
                    break;
                    
                case 'lists':
                    $lists = $this->extractLists($pageText);
                    $structuredData[] = [
                        'page' => $pageNumber,
                        'lists' => $lists,
                    ];
                    break;
                    
                case 'metadata':
                    $metadata = [
                        'title' => $pdf->getDetails()->getTitle(),
                        'author' => $pdf->getDetails()->getAuthor(),
                        'subject' => $pdf->getDetails()->getSubject(),
                        'creator' => $pdf->getDetails()->getCreator(),
                        'producer' => $pdf->getDetails()->getProducer(),
                        'creation_date' => $pdf->getDetails()->getCreationDate(),
                        'modification_date' => $pdf->getDetails()->getModificationDate(),
                        'page_count' => $pageCount,
                    ];
                    break;
                    
                case 'keywords':
                    $keywords = $this->extractKeywords($pageText);
                    $structuredData[] = [
                        'page' => $pageNumber,
                        'keywords' => $keywords,
                    ];
                    break;
                    
                case 'entities':
                    $entities = $this->extractEntities($pageText);
                    $structuredData[] = [
                        'page' => $pageNumber,
                        'entities' => $entities,
                    ];
                    break;
            }
        }
        
        $processingTime = microtime(true) - $startTime;
        $wordCount = str_word_count($extractedText);
        $characterCount = strlen($extractedText);
        
        return [
            'text' => $extractedText,
            'structured_data' => $structuredData,
            'metadata' => $metadata,
            'processing_time' => $processingTime,
            'quality_score' => $this->calculateQualityScore($extractedText, $options),
            'word_count' => $wordCount,
            'character_count' => $characterCount,
            'page_count' => $pageCount,
        ];
    }

    /**
     * Extract text from text-based files
     */
    private function extractFromText(string $filePath, string $extractionType, array $options): array
    {
        $startTime = microtime(true);
        $content = file_get_contents($filePath);
        $extractedText = $content;
        $structuredData = [];
        $metadata = [];
        
        switch ($extractionType) {
            case 'full_text':
                // Use content as is
                break;
                
            case 'structured_text':
                $structuredData = $this->structureTextContent($content);
                break;
                
            case 'tables':
                $structuredData = $this->extractTablesFromText($content);
                break;
                
            case 'headings':
                $structuredData = $this->extractHeadingsFromText($content);
                break;
                
            case 'lists':
                $structuredData = $this->extractListsFromText($content);
                break;
                
            case 'keywords':
                $structuredData = $this->extractKeywords($content);
                break;
                
            case 'entities':
                $structuredData = $this->extractEntities($content);
                break;
        }
        
        $processingTime = microtime(true) - $startTime;
        $wordCount = str_word_count($extractedText);
        $characterCount = strlen($extractedText);
        
        return [
            'text' => $extractedText,
            'structured_data' => $structuredData,
            'metadata' => $metadata,
            'processing_time' => $processingTime,
            'quality_score' => $this->calculateQualityScore($extractedText, $options),
            'word_count' => $wordCount,
            'character_count' => $characterCount,
            'page_count' => 1,
        ];
    }

    /**
     * Extract text from images using OCR
     */
    private function extractFromImage(string $filePath, string $extractionType, array $options): array
    {
        $startTime = microtime(true);
        // This would integrate with the OCR service
        // For now, return placeholder data
        $extractedText = "Image text extraction would be performed here using OCR.";
        $structuredData = [];
        $metadata = [
            'image_type' => pathinfo($filePath, PATHINFO_EXTENSION),
            'extraction_method' => 'OCR (placeholder)',
        ];
        
        $processingTime = microtime(true) - $startTime;
        $wordCount = str_word_count($extractedText);
        $characterCount = strlen($extractedText);
        
        return [
            'text' => $extractedText,
            'structured_data' => $structuredData,
            'metadata' => $metadata,
            'processing_time' => $processingTime,
            'quality_score' => 75.0, // Lower quality for OCR
            'word_count' => $wordCount,
            'character_count' => $characterCount,
            'page_count' => 1,
        ];
    }

    /**
     * Analyze page layout
     */
    private function analyzeLayout($page): array
    {
        // This would analyze the visual layout of the page
        // For now, return basic structure
        return [
            'columns' => 1,
            'orientation' => 'portrait',
            'text_blocks' => [],
        ];
    }

    /**
     * Extract tables from text
     */
    private function extractTables(string $text): array
    {
        $tables = [];
        
        // Simple table detection using regex patterns
        $lines = explode("\n", $text);
        $currentTable = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Detect table rows (lines with multiple columns separated by spaces/tabs)
            if (preg_match('/^(.+?)\s{2,}(.+?)(?:\s{2,}(.+?))?$/', $line)) {
                $columns = preg_split('/\s{2,}/', $line);
                $currentTable[] = $columns;
            } elseif (!empty($currentTable)) {
                // End of table
                if (count($currentTable) > 1) {
                    $tables[] = $currentTable;
                }
                $currentTable = [];
            }
        }
        
        // Add last table if exists
        if (!empty($currentTable) && count($currentTable) > 1) {
            $tables[] = $currentTable;
        }
        
        return $tables;
    }

    /**
     * Extract form fields from text
     */
    private function extractFormFields(string $text): array
    {
        $formFields = [];
        
        // Detect common form field patterns
        $patterns = [
            'name' => '/name[:\s]*([^\n]+)/i',
            'email' => '/email[:\s]*([^\n]+)/i',
            'phone' => '/phone[:\s]*([^\n]+)/i',
            'address' => '/address[:\s]*([^\n]+)/i',
            'date' => '/date[:\s]*([^\n]+)/i',
        ];
        
        foreach ($patterns as $fieldType => $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                $formFields[$fieldType] = $matches[1];
            }
        }
        
        return $formFields;
    }

    /**
     * Extract headings from text
     */
    private function extractHeadings(string $text): array
    {
        $headings = [];
        
        // Detect headings by capitalization and length
        $lines = explode("\n", $text);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Simple heading detection
            if (strlen($line) > 3 && strlen($line) < 100 && 
                preg_match('/^[A-Z][A-Z\s\d]+$/', $line)) {
                $headings[] = $line;
            }
        }
        
        return $headings;
    }

    /**
     * Extract lists from text
     */
    private function extractLists(string $text): array
    {
        $lists = [];
        
        // Detect bullet points and numbered lists
        $lines = explode("\n", $text);
        $currentList = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Detect list items
            if (preg_match('/^[\-\*•]\s+(.+)$/', $line, $matches)) {
                $currentList[] = $matches[1];
            } elseif (preg_match('/^\d+\.\s+(.+)$/', $line, $matches)) {
                $currentList[] = $matches[1];
            } elseif (!empty($currentList)) {
                // End of list
                $lists[] = $currentList;
                $currentList = [];
            }
        }
        
        // Add last list if exists
        if (!empty($currentList)) {
            $lists[] = $currentList;
        }
        
        return $lists;
    }

    /**
     * Extract keywords from text
     */
    private function extractKeywords(string $text): array
    {
        $keywords = [];
        
        // Simple keyword extraction (remove common words and count frequency)
        $words = str_word_count(strtolower($text), 1);
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'can', 'this', 'that', 'these', 'those'];
        
        $wordCount = array_count_values($words);
        
        foreach ($wordCount as $word => $count) {
            if (!in_array($word, $stopWords) && strlen($word) > 3 && $count > 1) {
                $keywords[] = [
                    'word' => $word,
                    'frequency' => $count,
                ];
            }
        }
        
        // Sort by frequency
        usort($keywords, function($a, $b) {
            return $b['frequency'] - $a['frequency'];
        });
        
        return array_slice($keywords, 0, 20); // Top 20 keywords
    }

    /**
     * Extract named entities from text
     */
    private function extractEntities(string $text): array
    {
        $entities = [
            'names' => [],
            'organizations' => [],
            'locations' => [],
            'dates' => [],
            'emails' => [],
            'urls' => [],
        ];
        
        // Extract emails
        if (preg_match_all('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $text, $matches)) {
            $entities['emails'] = $matches[0];
        }
        
        // Extract URLs
        if (preg_match_all('/\bhttps?:\/\/[^\s]+/', $text, $matches)) {
            $entities['urls'] = $matches[0];
        }
        
        // Extract dates
        if (preg_match_all('/\b\d{1,2}[\/\-]\d{1,2}[\/\-]\d{2,4}\b/', $text, $matches)) {
            $entities['dates'] = $matches[0];
        }
        
        // Simple name detection (capitalized words)
        $words = explode(' ', $text);
        foreach ($words as $word) {
            $word = trim($word, '.,!?;:');
            if (preg_match('/^[A-Z][a-z]+$/', $word) && strlen($word) > 2) {
                $entities['names'][] = $word;
            }
        }
        
        return $entities;
    }

    /**
     * Structure text content
     */
    private function structureTextContent(string $content): array
    {
        $lines = explode("\n", $content);
        $structured = [
            'paragraphs' => [],
            'sections' => [],
        ];
        
        $currentParagraph = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                if (!empty($currentParagraph)) {
                    $structured['paragraphs'][] = $currentParagraph;
                    $currentParagraph = '';
                }
            } else {
                $currentParagraph .= $line . ' ';
            }
        }
        
        if (!empty($currentParagraph)) {
            $structured['paragraphs'][] = trim($currentParagraph);
        }
        
        return $structured;
    }

    /**
     * Extract tables from text content
     */
    private function extractTablesFromText(string $content): array
    {
        return $this->extractTables($content);
    }

    /**
     * Extract headings from text content
     */
    private function extractHeadingsFromText(string $content): array
    {
        return $this->extractHeadings($content);
    }

    /**
     * Extract lists from text content
     */
    private function extractListsFromText(string $content): array
    {
        return $this->extractLists($content);
    }

    /**
     * Calculate quality score for extracted text
     */
    private function calculateQualityScore(string $text, array $options): float
    {
        $score = 100.0;
        
        // Reduce score for empty text
        if (empty(trim($text))) {
            return 0.0;
        }
        
        // Reduce score for very short text
        if (strlen($text) < 10) {
            $score -= 20;
        }
        
        // Reduce score for text with many special characters
        $specialCharRatio = preg_match_all('/[^a-zA-Z0-9\s]/', $text) / strlen($text);
        if ($specialCharRatio > 0.3) {
            $score -= 15;
        }
        
        // Boost score for well-structured text
        if (preg_match('/\n\n/', $text)) {
            $score += 5;
        }
        
        return max(0, min(100, $score));
    }

    /**
     * Get extraction result
     */
    public function getExtractionResult(File $file, string $extractionType): ?TextExtraction
    {
        return TextExtraction::where('file_id', $file->id)
            ->where('extraction_type', $extractionType)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first();
    }

    /**
     * Get supported extraction types
     */
    public function getSupportedExtractionTypes(): array
    {
        return self::EXTRACTION_TYPES;
    }

    /**
     * Get extraction statistics
     */
    public function getExtractionStats(): array
    {
        $totalExtractions = TextExtraction::count();
        $successful = TextExtraction::where('status', 'completed')->count();
        $failed = TextExtraction::where('status', 'failed')->count();
        $averageProcessingTime = TextExtraction::where('status', 'completed')->avg('processing_time');
        $averageQualityScore = TextExtraction::where('status', 'completed')->avg('quality_score');
        
        return [
            'total_extractions' => $totalExtractions,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $totalExtractions > 0 ? ($successful / $totalExtractions) * 100 : 0,
            'average_processing_time' => round($averageProcessingTime, 2),
            'average_quality_score' => round($averageQualityScore, 2),
        ];
    }

    /**
     * Log extraction activity
     */
    private function logExtractionActivity(File $file, TextExtraction $extraction): void
    {
        $user = \Auth::user();
        
        if ($user) {
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'text_extracted',
                $file->id,
                null,
                [
                    'extraction_id' => $extraction->id,
                    'extraction_type' => $extraction->extraction_type,
                    'word_count' => $extraction->word_count,
                    'quality_score' => $extraction->quality_score,
                    'processing_time' => $extraction->processing_time,
                    'file_name' => $file->name,
                ]
            );
        }
    }

    /**
     * Check if file is suitable for text extraction
     */
    public function isExtractionSuitable(File $file): bool
    {
        $suitableTypes = [
            'application/pdf',
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'application/xml',
            'text/xml',
            'text/csv',
            'text/markdown',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp',
        ];
        
        return in_array($file->mime_type, $suitableTypes);
    }

    /**
     * Get extraction queue status
     */
    public function getQueueStatus(): array
    {
        return [
            'queue_size' => 0,
            'processing' => TextExtraction::where('status', 'processing')->count(),
            'completed_today' => TextExtraction::whereDate('created_at', today())->where('status', 'completed')->count(),
            'failed_today' => TextExtraction::whereDate('created_at', today())->where('status', 'failed')->count(),
        ];
    }
}
