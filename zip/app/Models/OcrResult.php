<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OcrResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'extracted_text',
        'confidence_score',
        'language',
        'processing_time',
        'ocr_engine',
        'ocr_version',
        'options',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:2',
        'processing_time' => 'decimal:2',
        'options' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Get the file this OCR result belongs to
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Scope for completed OCR results
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed OCR results
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for pending OCR results
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing OCR results
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for high confidence results
     */
    public function scopeHighConfidence($query, float $threshold = 80.0)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }

    /**
     * Scope for low confidence results
     */
    public function scopeLowConfidence($query, float $threshold = 50.0)
    {
        return $query->where('confidence_score', '<', $threshold);
    }

    /**
     * Scope for specific language
     */
    public function scopeForLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Scope for recent results
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('processed_at', '>=', now()->subDays($days));
    }

    /**
     * Check if OCR result is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if OCR result failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if OCR result is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if OCR result is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Get confidence level description
     */
    public function getConfidenceLevelAttribute(): string
    {
        if ($this->confidence_score >= 90) {
            return 'Excellent';
        } elseif ($this->confidence_score >= 80) {
            return 'Good';
        } elseif ($this->confidence_score >= 70) {
            return 'Fair';
        } elseif ($this->confidence_score >= 50) {
            return 'Poor';
        } else {
            return 'Very Poor';
        }
    }

    /**
     * Get confidence level color class
     */
    public function getConfidenceColorAttribute(): string
    {
        if ($this->confidence_score >= 90) {
            return 'success';
        } elseif ($this->confidence_score >= 80) {
            return 'info';
        } elseif ($this->confidence_score >= 70) {
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
     * Get word count of extracted text
     */
    public function getWordCountAttribute(): int
    {
        return str_word_count($this->extracted_text);
    }

    /**
     * Get character count of extracted text
     */
    public function getCharacterCountAttribute(): int
    {
        return strlen($this->extracted_text);
    }

    /**
     * Get language name
     */
    public function getLanguageNameAttribute(): string
    {
        $languages = [
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

        return $languages[$this->language] ?? $this->language;
    }

    /**
     * Get OCR options as formatted string
     */
    public function getFormattedOptionsAttribute(): string
    {
        if (empty($this->options)) {
            return 'Default';
        }

        $formatted = [];
        
        if (isset($this->options['language'])) {
            $formatted[] = 'Language: ' . $this->language_name;
        }
        
        if (isset($this->options['psm'])) {
            $formatted[] = 'PSM: ' . $this->options['psm'];
        }
        
        if (isset($this->options['oem'])) {
            $formatted[] = 'OEM: ' . $this->options['oem'];
        }

        return implode(', ', $formatted);
    }
}
