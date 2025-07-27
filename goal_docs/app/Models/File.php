<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'original_name',
        'file_path',
        'file_hash',
        'file_size',
        'mime_type',
        'extension',
        'folder_id',
        'user_type',
        'type_name',
        'uploaded_by',
        'metadata',
        'is_active',
        'last_accessed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'last_accessed_at' => 'datetime',
    ];

    /**
     * Get the folder this file belongs to
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the user who uploaded this file
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get all versions of this file
     */
    public function versions(): HasMany
    {
        return $this->hasMany(FileVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Get all permissions for this file
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(FilePermission::class);
    }

    /**
     * Get the current version of this file
     */
    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(FileVersion::class, 'id', 'file_id')
            ->where('is_current', true);
    }

    /**
     * Scope for filtering by user type
     */
    public function scopeForUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope for filtering by specific organization
     */
    public function scopeForOrganization($query, $userType, $typeName)
    {
        return $query->where('user_type', $userType)->where('type_name', $typeName);
    }

    /**
     * Scope for active files
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for files in root (no folder)
     */
    public function scopeInRoot($query)
    {
        return $query->whereNull('folder_id');
    }

    /**
     * Get human readable file size
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image
     */
    public function getIsImageAttribute(): bool
    {
        return strpos($this->mime_type, 'image/') === 0;
    }

    /**
     * Check if file is a document
     */
    public function getIsDocumentAttribute(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
        ];

        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Check if file is a video
     */
    public function getIsVideoAttribute(): bool
    {
        return strpos($this->mime_type, 'video/') === 0;
    }

    /**
     * Check if file is audio
     */
    public function getIsAudioAttribute(): bool
    {
        return strpos($this->mime_type, 'audio/') === 0;
    }

    /**
     * Get the file's URL for download
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('files.download', $this->id);
    }

    /**
     * Get the file's URL for preview
     */
    public function getPreviewUrlAttribute(): string
    {
        return route('files.preview', $this->id);
    }

    /**
     * Get file icon based on type
     */
    public function getIconAttribute(): string
    {
        if ($this->is_image) {
            return 'ti ti-photo';
        } elseif ($this->is_document) {
            return 'ti ti-file-text';
        } elseif ($this->is_video) {
            return 'ti ti-video';
        } elseif ($this->is_audio) {
            return 'ti ti-music';
        } elseif ($this->extension === 'zip' || $this->extension === 'rar') {
            return 'ti ti-archive';
        } else {
            return 'ti ti-file';
        }
    }

    /**
     * Update last accessed timestamp
     */
    public function updateLastAccessed(): void
    {
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Get the full folder path for this file
     */
    public function getFolderPathAttribute(): string
    {
        if (!$this->folder) {
            return 'Root';
        }
        
        return $this->folder->full_path;
    }

    /**
     * Delete file from storage and database
     */
    public function deleteFile(): bool
    {
        // Delete all versions from storage
        foreach ($this->versions as $version) {
            Storage::delete($version->file_path);
        }
        
        // Delete current file from storage
        Storage::delete($this->file_path);
        
        // Delete from database
        return $this->delete();
    }
}
