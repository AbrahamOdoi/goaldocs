<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TextExtraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'extraction_type',
        'extracted_text',
        'structured_data',
        'metadata',
        'processing_time',
        'quality_score',
        'word_count',
        'character_count',
        'page_count',
        'options',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'structured_data' => 'array',
        'metadata' => 'array',
        'options' => 'array',
        'processing_time' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'word_count' => 'integer',
        'character_count' => 'integer',
        'page_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Get the file this extraction belongs to
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Scope for completed extractions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed extractions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for pending extractions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing extractions
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for high quality extractions
     */
    public function scopeHighQuality($query, float $threshold = 80.0)
    {
        return $query->where('quality_score', '>=', $threshold);
    }

    /**
     * Scope for low quality extractions
     */
    public function scopeLowQuality($query, float $threshold = 50.0)
    {
        return $query->where('quality_score', '<', $threshold);
    }

    /**
     * Scope for specific extraction type
     */
    public function scopeForType($query, string $type)
    {
        return $query->where('extraction_type', $type);
    }

    /**
     * Scope for recent extractions
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if extraction is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if extraction failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if extraction is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if extraction is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Get quality level description
     */
    public function getQualityLevelAttribute(): string
    {
        if ($this->quality_score >= 90) {
            return 'Excellent';
        } elseif ($this->quality_score >= 80) {
            return 'Good';
        } elseif ($this->quality_score >= 70) {
            return 'Fair';
        } elseif ($this->quality_score >= 50) {
            return 'Poor';
        } else {
            return 'Very Poor';
        }
    }

    /**
     * Get quality level color class
     */
    public function getQualityColorAttribute(): string
    {
        if ($this->quality_score >= 90) {
            return 'success';
        } elseif ($this->quality_score >= 80) {
            return 'info';
        } elseif ($this->quality_score >= 70) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    /**
     * Get formatted processing time
     */
    public function getFormattedProcessingTimeAttribute(): string
    {
        if ($this->processing_time < 1) {
            return round($this->processing_time * 1000, 0) . 'ms';
        } else {
            return round($this->processing_time, 2) . 's';
        }
    }

    /**
     * Get extraction type description
     */
    public function getExtractionTypeDescriptionAttribute(): string
    {
        $descriptions = [
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

        return $descriptions[$this->extraction_type] ?? $this->extraction_type;
    }

    /**
     * Get extraction type icon
     */
    public function getExtractionTypeIconAttribute(): string
    {
        $icons = [
            'full_text' => 'ti ti-file-text',
            'structured_text' => 'ti ti-layout-text',
            'tables' => 'ti ti-table',
            'forms' => 'ti ti-clipboard-list',
            'headings' => 'ti ti-heading',
            'lists' => 'ti ti-list',
            'metadata' => 'ti ti-info-circle',
            'keywords' => 'ti ti-tag',
            'entities' => 'ti ti-user-check',
        ];

        return $icons[$this->extraction_type] ?? 'ti ti-file';
    }

    /**
     * Get extraction type color
     */
    public function getExtractionTypeColorAttribute(): string
    {
        $colors = [
            'full_text' => 'primary',
            'structured_text' => 'info',
            'tables' => 'success',
            'forms' => 'warning',
            'headings' => 'secondary',
            'lists' => 'dark',
            'metadata' => 'light',
            'keywords' => 'danger',
            'entities' => 'primary',
        ];

        return $colors[$this->extraction_type] ?? 'primary';
    }

    /**
     * Get formatted word count
     */
    public function getFormattedWordCountAttribute(): string
    {
        if ($this->word_count >= 1000000) {
            return round($this->word_count / 1000000, 1) . 'M words';
        } elseif ($this->word_count >= 1000) {
            return round($this->word_count / 1000, 1) . 'K words';
        } else {
            return $this->word_count . ' words';
        }
    }

    /**
     * Get formatted character count
     */
    public function getFormattedCharacterCountAttribute(): string
    {
        if ($this->character_count >= 1000000) {
            return round($this->character_count / 1000000, 1) . 'M chars';
        } elseif ($this->character_count >= 1000) {
            return round($this->character_count / 1000, 1) . 'K chars';
        } else {
            return $this->character_count . ' chars';
        }
    }

    /**
     * Get extraction options as formatted string
     */
    public function getFormattedOptionsAttribute(): string
    {
        if (empty($this->options)) {
            return 'Default';
        }

        $formatted = [];
        
        if (isset($this->options['type'])) {
            $formatted[] = 'Type: ' . $this->extraction_type_description;
        }
        
        if (isset($this->options['language'])) {
            $formatted[] = 'Language: ' . $this->options['language'];
        }
        
        if (isset($this->options['quality'])) {
            $formatted[] = 'Quality: ' . $this->options['quality'];
        }

        return implode(', ', $formatted);
    }

    /**
     * Get extraction duration
     */
    public function getDurationAttribute(): string
    {
        if (!$this->started_at || !$this->completed_at) {
            return 'N/A';
        }

        $duration = $this->completed_at->diffInSeconds($this->started_at);
        
        if ($duration < 60) {
            return $duration . 's';
        } elseif ($duration < 3600) {
            return floor($duration / 60) . 'm ' . ($duration % 60) . 's';
        } else {
            $hours = floor($duration / 3600);
            $minutes = floor(($duration % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }

    /**
     * Get structured data summary
     */
    public function getStructuredDataSummaryAttribute(): array
    {
        if (empty($this->structured_data)) {
            return [];
        }

        $summary = [];
        
        foreach ($this->structured_data as $key => $value) {
            if (is_array($value)) {
                $summary[$key] = count($value);
            } else {
                $summary[$key] = 1;
            }
        }
        
        return $summary;
    }

    /**
     * Get metadata summary
     */
    public function getMetadataSummaryAttribute(): array
    {
        if (empty($this->metadata)) {
            return [];
        }

        return array_slice($this->metadata, 0, 5); // First 5 metadata items
    }
}
