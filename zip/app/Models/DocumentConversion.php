<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'source_format',
        'target_format',
        'conversion_type',
        'output_file_path',
        'output_file_size',
        'processing_time',
        'quality_score',
        'options',
        'status',
        'error_message',
        'started_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'processing_time' => 'decimal:2',
        'quality_score' => 'decimal:2',
        'options' => 'array',
        'metadata' => 'array',
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
     * Get the file this conversion belongs to
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Scope for completed conversions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed conversions
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for pending conversions
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing conversions
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for high quality conversions
     */
    public function scopeHighQuality($query, float $threshold = 80.0)
    {
        return $query->where('quality_score', '>=', $threshold);
    }

    /**
     * Scope for low quality conversions
     */
    public function scopeLowQuality($query, float $threshold = 50.0)
    {
        return $query->where('quality_score', '<', $threshold);
    }

    /**
     * Scope for specific conversion type
     */
    public function scopeForType($query, string $type)
    {
        return $query->where('conversion_type', $type);
    }

    /**
     * Scope for recent conversions
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if conversion is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if conversion failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if conversion is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if conversion is processing
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
     * Get human readable output file size
     */
    public function getHumanOutputSizeAttribute(): string
    {
        $bytes = $this->output_file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get conversion type description
     */
    public function getConversionTypeDescriptionAttribute(): string
    {
        $descriptions = [
            'pdf_to_word' => 'PDF to Word Document',
            'pdf_to_excel' => 'PDF to Excel Spreadsheet',
            'pdf_to_powerpoint' => 'PDF to PowerPoint Presentation',
            'word_to_pdf' => 'Word Document to PDF',
            'excel_to_pdf' => 'Excel Spreadsheet to PDF',
            'powerpoint_to_pdf' => 'PowerPoint Presentation to PDF',
            'image_to_pdf' => 'Image to PDF',
            'pdf_to_image' => 'PDF to Image',
            'image_format_conversion' => 'Image Format Conversion',
        ];

        return $descriptions[$this->conversion_type] ?? $this->conversion_type;
    }

    /**
     * Get source format extension
     */
    public function getSourceExtensionAttribute(): string
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

        return $extensions[$this->source_format] ?? 'bin';
    }

    /**
     * Get target format extension
     */
    public function getTargetExtensionAttribute(): string
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

        return $extensions[$this->target_format] ?? 'bin';
    }

    /**
     * Get download URL for converted file
     */
    public function getDownloadUrlAttribute(): string
    {
        if ($this->output_file_path && $this->isSuccessful()) {
            return route('files.conversion.download', $this->id);
        }
        
        return '';
    }

    /**
     * Get preview URL for converted file
     */
    public function getPreviewUrlAttribute(): string
    {
        if ($this->output_file_path && $this->isSuccessful()) {
            return route('files.conversion.preview', $this->id);
        }
        
        return '';
    }

    /**
     * Get conversion options as formatted string
     */
    public function getFormattedOptionsAttribute(): string
    {
        if (empty($this->options)) {
            return 'Default';
        }

        $formatted = [];
        
        if (isset($this->options['quality'])) {
            $formatted[] = 'Quality: ' . $this->options['quality'];
        }
        
        if (isset($this->options['max_width'])) {
            $formatted[] = 'Max Width: ' . $this->options['max_width'];
        }
        
        if (isset($this->options['max_height'])) {
            $formatted[] = 'Max Height: ' . $this->options['max_height'];
        }
        
        if (isset($this->options['rotate'])) {
            $formatted[] = 'Rotate: ' . $this->options['rotate'] . '°';
        }

        return implode(', ', $formatted);
    }

    /**
     * Get conversion duration
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
}
