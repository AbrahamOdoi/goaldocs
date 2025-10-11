<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'file_id',
        'folder_id',
        'user_type',
        'type_name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who favorited this resource
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the favorited file
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the favorited folder
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the favorited resource (file or folder)
     */
    public function resource()
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
     * Scope for filtering by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if a user has favorited a resource
     */
    public static function isFavorited($userId, $fileId = null, $folderId = null): bool
    {
        $query = static::where('user_id', $userId);
        
        if ($fileId) {
            $query->where('file_id', $fileId);
        } elseif ($folderId) {
            $query->where('folder_id', $folderId);
        }
        
        return $query->exists();
    }

    /**
     * Toggle favorite status for a resource
     */
    public static function toggleFavorite($userId, $userType, $typeName, $fileId = null, $folderId = null): bool
    {
        $existing = static::where('user_id', $userId);
        
        if ($fileId) {
            $existing->where('file_id', $fileId);
        } elseif ($folderId) {
            $existing->where('folder_id', $folderId);
        }
        
        $existing = $existing->first();
        
        if ($existing) {
            $existing->delete();
            return false; // Unfavorited
        } else {
            static::create([
                'user_id' => $userId,
                'file_id' => $fileId,
                'folder_id' => $folderId,
                'user_type' => $userType,
                'type_name' => $typeName,
            ]);
            return true; // Favorited
        }
    }
}
