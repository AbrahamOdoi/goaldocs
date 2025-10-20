<?php

/**
 * File Model - Core Document Management Entity for GoalDocs Enterprise System
 * 
 * This model represents the central file entity in the GoalDocs document management system,
 * handling file storage, metadata, permissions, versioning, and search capabilities.
 * 
 * Key Features:
 * - Multi-format file support with type detection and validation
 * - Advanced search with full-text indexing and content extraction
 * - Version control and file history tracking
 * - Permission-based access control
 * - OCR and text extraction capabilities
 * - Preview generation and thumbnail support
 * - Activity tracking and audit logging
 * 
 * Supported File Types:
 * - Documents: PDF, Word, Excel, PowerPoint, Text files
 * - Images: JPEG, PNG, GIF, BMP, TIFF
 * - Media: Video and audio files
 * - Archives: ZIP, RAR, and other compressed formats
 * 
 * @package App\Models
 * @author GoalDocs Development Team
 * @version 1.0.0
 * @since 2024
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class File extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',              // Display name for the file
        'original_name',     // Original filename when uploaded
        'file_path',         // Storage path to the physical file
        'mime_type',         // MIME type for content validation
        'file_size',         // File size in bytes
        'extension',         // File extension (e.g., 'pdf', 'docx')
        'description',       // User-provided file description
        'folder_id',         // Parent folder ID (null for root)
        'uploaded_by',       // User ID who uploaded the file
        'user_type',         // User type context (individual, organisation, etc.)
        'user_type_name',    // Specific organization/entity name
        'metadata',          // JSON metadata (dimensions, properties, etc.)
        'extracted_text',    // OCR/extracted text content for search
        'search_metadata',   // JSON search optimization data
        'indexed_at',        // Timestamp when file was indexed for search
        'last_accessed_at',  // Last access timestamp for analytics
    ];

    /**
     * The attributes that should be cast.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',           // JSON metadata storage
        'search_metadata' => 'array',    // JSON search optimization data
        'indexed_at' => 'datetime',      // Search indexing timestamp
        'last_accessed_at' => 'datetime', // Last access timestamp
    ];

    /**
     * Get the folder this file belongs to.
     * 
     * Returns the parent folder containing this file. Returns null if the file
     * is in the root directory.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the user who uploaded this file.
     * 
     * Returns the user entity that originally uploaded this file for
     * ownership tracking and permission inheritance.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get all versions of this file.
     * 
     * Returns all historical versions of this file for version control,
     * rollback capabilities, and change tracking.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function versions(): HasMany
    {
        return $this->hasMany(FileVersion::class);
    }

    /**
     * Get all permissions assigned to this file.
     * 
     * Returns all permission records that control access to this file,
     * including user, position, and department-level permissions.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
     * Get human readable file size.
     * 
     * Converts file size from bytes to a human-readable format (KB, MB, GB, TB).
     * Used for display purposes in the user interface.
     * 
     * @return string Human-readable file size (e.g., "2.5 MB")
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->file_size; // Fixed: use file_size instead of size
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is an image.
     * 
     * Determines if the file is an image based on MIME type.
     * Used for preview generation and UI display logic.
     * 
     * @return bool True if file is an image
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if file is a document.
     * 
     * Determines if the file is a document that supports text extraction
     * and full-text search capabilities.
     * 
     * @return bool True if file is a supported document type
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
            return asset('storage/' . $this->search_metadata['preview_path']);
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
     * Delete the physical file from storage and database record
     */
    public function deleteFile(): bool
    {
        try {
            // Delete the main file from storage
            if (Storage::disk('local')->exists($this->file_path)) {
                Storage::disk('local')->delete($this->file_path);
            }
            
            // Delete preview if exists
            if ($this->search_metadata && isset($this->search_metadata['preview_path'])) {
                if (Storage::disk('local')->exists($this->search_metadata['preview_path'])) {
                    Storage::disk('local')->delete($this->search_metadata['preview_path']);
                }
            }
            
            // Delete all related records first (to maintain referential integrity)
            $this->versions()->delete();
            $this->permissions()->delete();
            $this->tags()->delete();
            $this->favorites()->delete();
            $this->comments()->delete();
            $this->documentLocks()->delete();
            $this->encryptionKeys()->delete();
            
            // Finally, delete the file record from database
            $this->delete();
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error deleting file {$this->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Scope for full-text search across file content.
     * 
     * Performs comprehensive search across file names, descriptions, and extracted text
     * using MySQL's full-text search capabilities with fallback to LIKE queries.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm The search term to look for
     * @return \Illuminate\Database\Eloquent\Builder
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
     * Scope for searching within extracted text content only.
     * 
     * Searches specifically within the OCR/extracted text content of files,
     * useful for finding documents containing specific text content.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm The search term to look for in content
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchInContent($query, $searchTerm)
    {
        return $query->where('extracted_text', 'like', "%{$searchTerm}%");
    }

    /**
     * Scope for files that have been indexed for search.
     * 
     * Returns files that have been processed and indexed for full-text search,
     * indicating they are ready for search operations.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIndexed($query)
    {
        return $query->whereNotNull('indexed_at');
    }

    /**
     * Scope for files that need indexing.
     * 
     * Returns files that have not yet been indexed for search, useful for
     * background processing and search optimization tasks.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
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
