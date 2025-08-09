<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncryptionKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'key_hash',
        'encryption_method',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the file this key belongs to
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who created this key
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get keys by encryption method
     */
    public function scopeByMethod($query, string $method)
    {
        return $query->where('encryption_method', $method);
    }

    /**
     * Scope to get keys by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to get recent keys
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>', now()->subDays($days));
    }

    /**
     * Check if key is recent (within last 7 days)
     */
    public function getIsRecentAttribute(): bool
    {
        return $this->created_at->isAfter(now()->subDays(7));
    }

    /**
     * Get encryption method display name
     */
    public function getMethodDisplayAttribute(): string
    {
        $methods = [
            'AES-256-CBC' => 'AES-256-CBC (Strong)',
            'AES-128-CBC' => 'AES-128-CBC (Standard)',
            'DES' => 'DES (Legacy)',
        ];

        return $methods[$this->encryption_method] ?? $this->encryption_method;
    }

    /**
     * Get key strength rating
     */
    public function getStrengthRatingAttribute(): string
    {
        $ratings = [
            'AES-256-CBC' => 'excellent',
            'AES-128-CBC' => 'good',
            'DES' => 'weak',
        ];

        return $ratings[$this->encryption_method] ?? 'unknown';
    }

    /**
     * Get strength color
     */
    public function getStrengthColorAttribute(): string
    {
        $colors = [
            'excellent' => 'success',
            'good' => 'info',
            'weak' => 'warning',
            'unknown' => 'secondary',
        ];

        return $colors[$this->strength_rating] ?? 'secondary';
    }

    /**
     * Get formatted creation date
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    /**
     * Get key age in days
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Check if key should be rotated (older than 90 days)
     */
    public function getShouldRotateAttribute(): bool
    {
        return $this->age_in_days > 90;
    }

    /**
     * Get file size from metadata
     */
    public function getFileSizeAttribute(): int
    {
        return $this->metadata['file_size'] ?? 0;
    }

    /**
     * Get encrypted file size from metadata
     */
    public function getEncryptedFileSizeAttribute(): int
    {
        return $this->metadata['encrypted_size'] ?? 0;
    }

    /**
     * Get compression ratio
     */
    public function getCompressionRatioAttribute(): float
    {
        if ($this->file_size === 0) {
            return 0;
        }

        return round(($this->encrypted_file_size / $this->file_size) * 100, 2);
    }

    /**
     * Get original file path from metadata
     */
    public function getOriginalFilePathAttribute(): string
    {
        return $this->metadata['original_path'] ?? '';
    }

    /**
     * Get encrypted file path from metadata
     */
    public function getEncryptedFilePathAttribute(): string
    {
        return $this->metadata['encrypted_path'] ?? '';
    }
}
