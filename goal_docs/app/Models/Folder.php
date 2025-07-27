<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'parent_folder_id',
        'user_type',
        'type_name',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the parent folder
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_folder_id');
    }

    /**
     * Get all child folders
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_folder_id');
    }

    /**
     * Get all active child folders
     */
    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    /**
     * Get all files in this folder
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get all active files in this folder
     */
    public function activeFiles(): HasMany
    {
        return $this->files()->where('is_active', true);
    }

    /**
     * Get the user who created this folder
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all permissions for this folder
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(FilePermission::class);
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
     * Scope for active folders
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for root folders (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_folder_id');
    }

    /**
     * Get the full path of the folder
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' / ', $path);
    }

    /**
     * Get all descendant folders recursively
     */
    public function getAllDescendants()
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    /**
     * Check if this folder is a descendant of another folder
     */
    public function isDescendantOf(Folder $folder): bool
    {
        $parent = $this->parent;
        
        while ($parent) {
            if ($parent->id === $folder->id) {
                return true;
            }
            $parent = $parent->parent;
        }
        
        return false;
    }

    /**
     * Get the total size of all files in this folder and subfolders
     */
    public function getTotalSizeAttribute(): int
    {
        $size = $this->files()->sum('file_size');
        
        foreach ($this->children as $child) {
            $size += $child->total_size;
        }
        
        return $size;
    }
}
