<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FileVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'version_number',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
        'change_notes',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    /**
     * Get the file this version belongs to
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the user who uploaded this version
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
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
     * Get the download URL for this version
     */
    public function getDownloadUrlAttribute(): string
    {
        return route('files.download-version', [$this->file_id, $this->id]);
    }

    /**
     * Make this version the current version
     */
    public function makeCurrent(): void
    {
        // Remove current flag from all versions of this file
        static::where('file_id', $this->file_id)->update(['is_current' => false]);
        
        // Set this version as current
        $this->update(['is_current' => true]);
        
        // Update the main file record to point to this version
        $this->file->update([
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
        ]);
    }

    /**
     * Delete this version from storage
     */
    public function deleteVersion(): bool
    {
        // Don't allow deletion of the current version
        if ($this->is_current) {
            return false;
        }
        
        // Delete file from storage
        Storage::delete($this->file_path);
        
        // Delete from database
        return $this->delete();
    }

    /**
     * Get the version label (e.g., "v1.0", "v2.1")
     */
    public function getVersionLabelAttribute(): string
    {
        return 'v' . $this->version_number . '.0';
    }
}
