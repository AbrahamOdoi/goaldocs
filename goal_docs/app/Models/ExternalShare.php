<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ExternalShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'folder_id',
        'share_token',
        'share_type',
        'recipient_email',
        'recipient_phone',
        'recipient_name',
        'password_protected',
        'password_hash',
        'expires_at',
        'max_downloads',
        'download_count',
        'view_count',
        'permissions',
        'user_type',
        'type_name',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'password_protected' => 'boolean',
        'expires_at' => 'datetime',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'max_downloads' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'share_url',
        'is_expired',
        'is_download_limit_reached',
        'is_valid',
        'resource_name',
        'resource_type',
    ];

    /**
     * Get the file being shared
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the folder being shared
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the user who created this share
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the resource being shared (file or folder)
     */
    public function getResourceAttribute()
    {
        return $this->file ?? $this->folder;
    }

    /**
     * Get the resource type
     */
    public function getResourceTypeAttribute(): string
    {
        return $this->file_id ? 'file' : 'folder';
    }

    /**
     * Get the resource name
     */
    public function getResourceNameAttribute(): string
    {
        $resource = $this->resource;
        return $resource ? $resource->name : 'Unknown Resource';
    }

    /**
     * Get the share URL
     */
    public function getShareUrlAttribute(): string
    {
        return url("/shared/{$this->share_token}");
    }

    /**
     * Check if the share is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return now()->isAfter($this->expires_at);
    }

    /**
     * Check if download limit is reached
     */
    public function getIsDownloadLimitReachedAttribute(): bool
    {
        if (!$this->max_downloads) {
            return false;
        }
        return $this->download_count >= $this->max_downloads;
    }

    /**
     * Check if the share is valid (not expired, not reached limit, active)
     */
    public function getIsValidAttribute(): bool
    {
        return $this->is_active && 
               !$this->is_expired && 
               !$this->is_download_limit_reached;
    }

    /**
     * Generate a unique share token
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::where('share_token', $token)->exists());

        return $token;
    }

    /**
     * Set password for the share
     */
    public function setPassword(string $password): void
    {
        $this->update([
            'password_protected' => true,
            'password_hash' => Hash::make($password),
        ]);
    }

    /**
     * Verify password for the share
     */
    public function verifyPassword(string $password): bool
    {
        if (!$this->password_protected) {
            return true;
        }
        return Hash::check($password, $this->password_hash);
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Increment download count
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions[$permission] ?? false;
    }

    /**
     * Get default permissions for external shares
     */
    public static function getDefaultPermissions(): array
    {
        return [
            'view' => true,
            'download' => true,
            'edit' => false,
            'upload' => false,
            'delete' => false,
            'reshare' => false,
            'manage' => false,
        ];
    }

    /**
     * Scope for active shares
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for valid shares (not expired, not reached limit)
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    })
                    ->where(function($q) {
                        $q->whereNull('max_downloads')
                          ->orWhereRaw('download_count < max_downloads');
                    });
    }

    /**
     * Scope for shares by organization
     */
    public function scopeForOrganization($query, $userType, $typeName)
    {
        return $query->where('user_type', $userType)
                    ->where('type_name', $typeName);
    }
}
