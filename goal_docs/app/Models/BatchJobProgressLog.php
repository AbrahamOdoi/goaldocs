<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchJobProgressLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_job_id',
        'message',
        'progress',
        'success',
        'processed_at',
    ];

    protected $casts = [
        'progress' => 'decimal:2',
        'success' => 'boolean',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the batch job this log belongs to
     */
    public function batchJob(): BelongsTo
    {
        return $this->belongsTo(BatchJob::class);
    }

    /**
     * Scope for successful logs
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope for failed logs
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('processed_at', '>=', now()->subHours($hours));
    }

    /**
     * Get formatted progress
     */
    public function getFormattedProgressAttribute(): string
    {
        return round($this->progress, 1) . '%';
    }

    /**
     * Get status icon
     */
    public function getStatusIconAttribute(): string
    {
        return $this->success ? 'ti ti-check text-success' : 'ti ti-x text-danger';
    }

    /**
     * Get formatted processed time
     */
    public function getFormattedProcessedTimeAttribute(): string
    {
        return $this->processed_at->format('M j, Y g:i:s');
    }
}
