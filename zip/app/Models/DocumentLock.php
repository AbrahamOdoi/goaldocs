<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLock extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'user_id',
        'user_type',
        'user_type_name',
        'locked_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the file that is locked
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who locked the document
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get only active locks
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope to get only expired locks
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Check if lock is still active
     */
    public function isActive(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Get remaining time in minutes
     */
    public function getRemainingMinutes(): int
    {
        return now()->diffInMinutes($this->expires_at, false);
    }
}
