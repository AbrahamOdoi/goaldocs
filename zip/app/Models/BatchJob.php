<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'operation_type',
        'file_ids',
        'total_files',
        'processed_files',
        'successful_files',
        'failed_files',
        'options',
        'status',
        'progress',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'file_ids' => 'array',
        'options' => 'array',
        'progress' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_COMPLETED_WITH_ERRORS = 'completed_with_errors';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the user who created this batch job
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the progress logs for this batch job
     */
    public function progressLogs(): HasMany
    {
        return $this->hasMany(BatchJobProgressLog::class);
    }

    /**
     * Scope for pending jobs
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for processing jobs
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope for completed jobs
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [self::STATUS_COMPLETED, self::STATUS_COMPLETED_WITH_ERRORS]);
    }

    /**
     * Scope for failed jobs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for cancelled jobs
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope for recent jobs
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if job is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if job is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if job is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_COMPLETED_WITH_ERRORS]);
    }

    /**
     * Check if job is failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if job is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if job has errors
     */
    public function hasErrors(): bool
    {
        return $this->status === self::STATUS_COMPLETED_WITH_ERRORS || $this->status === self::STATUS_FAILED;
    }

    /**
     * Get operation type description
     */
    public function getOperationTypeDescriptionAttribute(): string
    {
        $descriptions = [
            'ocr' => 'OCR Processing',
            'conversion' => 'Format Conversion',
            'text_extraction' => 'Text Extraction',
            'thumbnail_generation' => 'Thumbnail Generation',
            'metadata_extraction' => 'Metadata Extraction',
        ];

        return $descriptions[$this->operation_type] ?? $this->operation_type;
    }

    /**
     * Get operation type icon
     */
    public function getOperationTypeIconAttribute(): string
    {
        $icons = [
            'ocr' => 'ti ti-scan',
            'conversion' => 'ti ti-refresh',
            'text_extraction' => 'ti ti-file-text',
            'thumbnail_generation' => 'ti ti-photo',
            'metadata_extraction' => 'ti ti-info-circle',
        ];

        return $icons[$this->operation_type] ?? 'ti ti-settings';
    }

    /**
     * Get operation type color
     */
    public function getOperationTypeColorAttribute(): string
    {
        $colors = [
            'ocr' => 'primary',
            'conversion' => 'info',
            'text_extraction' => 'success',
            'thumbnail_generation' => 'warning',
            'metadata_extraction' => 'secondary',
        ];

        return $colors[$this->operation_type] ?? 'primary';
    }

    /**
     * Get status color class
     */
    public function getStatusColorAttribute(): string
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 'warning';
            case self::STATUS_PROCESSING:
                return 'info';
            case self::STATUS_COMPLETED:
                return 'success';
            case self::STATUS_COMPLETED_WITH_ERRORS:
                return 'warning';
            case self::STATUS_FAILED:
                return 'danger';
            case self::STATUS_CANCELLED:
                return 'secondary';
            default:
                return 'primary';
        }
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        return "badge bg-{$this->status_color}";
    }

    /**
     * Get formatted progress
     */
    public function getFormattedProgressAttribute(): string
    {
        return round($this->progress, 1) . '%';
    }

    /**
     * Get success rate
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->total_files === 0) {
            return 0.0;
        }

        return round(($this->successful_files / $this->total_files) * 100, 1);
    }

    /**
     * Get failure rate
     */
    public function getFailureRateAttribute(): float
    {
        if ($this->total_files === 0) {
            return 0.0;
        }

        return round(($this->failed_files / $this->total_files) * 100, 1);
    }

    /**
     * Get duration
     */
    public function getDurationAttribute(): string
    {
        if (!$this->started_at) {
            return 'N/A';
        }

        $endTime = $this->completed_at ?? now();
        $duration = $endTime->diffInSeconds($this->started_at);
        
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
     * Get estimated time remaining
     */
    public function getEstimatedTimeRemainingAttribute(): string
    {
        if (!$this->started_at || $this->progress === 0) {
            return 'N/A';
        }

        $elapsed = now()->diffInSeconds($this->started_at);
        $totalEstimated = ($elapsed / $this->progress) * 100;
        $remaining = $totalEstimated - $elapsed;

        if ($remaining < 60) {
            return round($remaining) . 's';
        } elseif ($remaining < 3600) {
            return floor($remaining / 60) . 'm ' . round($remaining % 60) . 's';
        } else {
            $hours = floor($remaining / 3600);
            $minutes = floor(($remaining % 3600) / 60);
            return $hours . 'h ' . $minutes . 'm';
        }
    }

    /**
     * Get formatted options
     */
    public function getFormattedOptionsAttribute(): string
    {
        if (empty($this->options)) {
            return 'Default';
        }

        $formatted = [];
        
        foreach ($this->options as $key => $value) {
            if (is_array($value)) {
                $formatted[] = ucfirst($key) . ': ' . count($value) . ' items';
            } else {
                $formatted[] = ucfirst($key) . ': ' . $value;
            }
        }

        return implode(', ', $formatted);
    }

    /**
     * Get files for this batch job
     */
    public function getFilesAttribute()
    {
        return File::whereIn('id', $this->file_ids)->get();
    }

    /**
     * Get recent progress logs
     */
    public function getRecentLogsAttribute()
    {
        return $this->progressLogs()
            ->orderBy('processed_at', 'desc')
            ->limit(10)
            ->get();
    }
}
