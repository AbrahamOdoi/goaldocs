<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'user_id',
        'status',
        'file_path',
        'file_size',
        'format',
        'generated_at',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // Format constants
    const FORMAT_PDF = 'pdf';
    const FORMAT_EXCEL = 'excel';
    const FORMAT_CSV = 'csv';

    /**
     * Get the report that owns the generation.
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the user that generated the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for completed generations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for failed generations.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope for pending generations.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Get status options.
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    /**
     * Get format options.
     */
    public static function getFormatOptions()
    {
        return [
            self::FORMAT_PDF => 'PDF',
            self::FORMAT_EXCEL => 'Excel',
            self::FORMAT_CSV => 'CSV',
        ];
    }

    /**
     * Check if generation is completed.
     */
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if generation failed.
     */
    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if generation is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if generation is processing.
     */
    public function isProcessing()
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSize()
    {
        if (!$this->file_size) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get download URL.
     */
    public function getDownloadUrl()
    {
        if (!$this->file_path || !$this->isCompleted()) {
            return null;
        }

        return route('reports.download', $this->id);
    }
}
