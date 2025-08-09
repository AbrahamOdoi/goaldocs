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
        'mime_type',
        'file_size',
        'extension',
        'description',
        'folder_id',
        'uploaded_by',
        'user_type',
        'user_type_name',
        'metadata',
        'extracted_text',
        'search_metadata',
        'indexed_at',
        'last_accessed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'search_metadata' => 'array',
        'indexed_at' => 'datetime',
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
     * Get the file versions
     */
    public function versions(): HasMany
    {
        return $this->hasMany(FileVersion::class);
    }

    /**
     * Get the file permissions
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(FilePermission::class);
    }

    /**
     * Get the file tags
     */
    public function tags(): HasMany
    {
        return $this->hasMany(FileTag::class);
    }

    /**
     * Get the file favorites
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * Get the file activities
     */
    public function activities(): HasMany
    {
        return $this->hasMany(RecentActivity::class);
    }

    /**
     * Get the current version of the file
     */
    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(FileVersion::class, 'current_version_id');
    }

    /**
     * Get the file comments
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'commentable_id')->where('commentable_type', File::class);
    }

    /**
     * Get the document locks
     */
    public function documentLocks(): HasMany
    {
        return $this->hasMany(DocumentLock::class);
    }

    /**
     * Get the workflow instances
     */
    public function workflowInstances(): HasMany
    {
        return $this->hasMany(WorkflowInstance::class);
    }

    /**
     * Get the encryption keys
     */
    public function encryptionKeys(): HasMany
    {
        return $this->hasMany(EncryptionKey::class);
    }

    /**
     * Scope for files by user type
     */
    public function scopeForUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    /**
     * Scope for files by organization
     */
    public function scopeForOrganization($query, $userType, $typeName)
    {
        return $query->where('user_type', $userType)
                    ->where('user_type_name', $typeName);
    }

    /**
     * Scope for active files
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope for files in root folder
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
        $bytes = $this->size;
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
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a document
     */
    public function getIsDocumentAttribute(): bool
    {
        $documentTypes = [
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
        ];
        
        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Check if file is a video
     */
    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Check if file is an audio file
     */
    public function getIsAudioAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Get download URL
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('files.download', $this->id);
    }

    /**
     * Get preview URL
     */
    public function getPreviewUrlAttribute(): ?string
    {
        if ($this->search_metadata && isset($this->search_metadata['preview_path'])) {
            return Storage::disk('local')->url($this->search_metadata['preview_path']);
        }
        
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
     * Delete the physical file from storage
     */
    public function deleteFile(): bool
    {
        try {
            // Delete the main file
            if (Storage::disk('local')->exists($this->file_path)) {
                Storage::disk('local')->delete($this->file_path);
            }
            
            // Delete preview if exists
            if ($this->search_metadata && isset($this->search_metadata['preview_path'])) {
                if (Storage::disk('local')->exists($this->search_metadata['preview_path'])) {
                    Storage::disk('local')->delete($this->search_metadata['preview_path']);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Error deleting file {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Scope for full-text search
     */
    public function scopeFullTextSearch($query, $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->whereRaw('MATCH(name, original_name, description, extracted_text) AGAINST(? IN BOOLEAN MODE)', [$searchTerm])
              ->orWhere('name', 'like', "%{$searchTerm}%")
              ->orWhere('original_name', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%")
              ->orWhere('extracted_text', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope for searching within specific content types
     */
    public function scopeSearchInContent($query, $searchTerm)
    {
        return $query->where('extracted_text', 'like', "%{$searchTerm}%");
    }

    /**
     * Scope for files that have been indexed for search
     */
    public function scopeIndexed($query)
    {
        return $query->whereNotNull('indexed_at');
    }

    /**
     * Scope for files that need indexing
     */
    public function scopeNeedsIndexing($query)
    {
        return $query->whereNull('indexed_at');
    }

    /**
     * Get searchable content for this file
     */
    public function getSearchableContent(): string
    {
        $content = [];
        
        if ($this->name) {
            $content[] = $this->name;
        }
        
        if ($this->original_name) {
            $content[] = $this->original_name;
        }
        
        if ($this->description) {
            $content[] = $this->description;
        }
        
        if ($this->extracted_text) {
            $content[] = $this->extracted_text;
        }
        
        return implode(' ', $content);
    }

    /**
     * Check if file supports text extraction
     */
    public function supportsTextExtraction(): bool
    {
        $supportedTypes = [
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        
        return in_array($this->mime_type, $supportedTypes);
    }

    /**
     * Check if file supports preview generation
     */
    public function supportsPreview(): bool
    {
        $supportedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/tiff',
        ];
        
        return in_array($this->mime_type, $supportedTypes);
    }

    /**
     * Get text content for search
     */
    public function getTextContentAttribute(): ?string
    {
        return $this->extracted_text;
    }

    /**
     * Get search relevance score (for ranking results)
     */
    public function getSearchRelevanceScore($searchTerm): float
    {
        $score = 0;
        $searchTerm = strtolower($searchTerm);
        
        // Name matches get highest score
        if (stripos($this->name, $searchTerm) !== false) {
            $score += 10;
        }
        
        // Original name matches
        if (stripos($this->original_name, $searchTerm) !== false) {
            $score += 8;
        }
        
        // Description matches
        if (stripos($this->description, $searchTerm) !== false) {
            $score += 5;
        }
        
        // Content matches
        if (stripos($this->extracted_text, $searchTerm) !== false) {
            $score += 3;
        }
        
        // Exact matches get bonus
        if (strtolower($this->name) === $searchTerm) {
            $score += 5;
        }
        
        return $score;
    }
}
