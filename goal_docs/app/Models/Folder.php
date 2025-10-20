<?php

/**
 * Folder Model - Hierarchical Directory Structure for GoalDocs Enterprise System
 * 
 * This model represents the folder/directory structure in the GoalDocs document management system,
 * providing hierarchical organization of files and folders with support for unlimited nesting depth.
 * 
 * Key Features:
 * - Unlimited nested folder hierarchy with parent-child relationships
 * - Multi-user type support with organizational context
 * - Permission-based access control at folder level
 * - Activity tracking and audit logging
 * - Size calculation and storage analytics
 * - Search capabilities with relevance scoring
 * - Soft deletion and folder management
 * 
 * Organizational Support:
 * - Individual: Personal folder structure
 * - Organization: Department-based folder organization
 * - Family: Role-based folder access
 * - Government: Agency-specific folder structure
 * - Educational: Academic department folders
 * - Social/Professional Groups: Community-based organization
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

class Folder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'name',              // Folder display name
        'description',       // Optional folder description
        'parent_folder_id',  // Parent folder ID (null for root folders)
        'user_type',         // User type context (individual, organisation, etc.)
        'type_name',         // Specific organization/entity name
        'created_by',        // User ID who created the folder
        'is_active',         // Folder status flag
        'is_system_folder',  // System-seeded folder flag
    ];

    /**
     * The attributes that should be cast.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean', // Cast to boolean for status checks
        'is_system_folder' => 'boolean', // Cast to boolean for system folder checks
    ];

    /**
     * Get the parent folder.
     * 
     * Returns the immediate parent folder in the hierarchy.
     * Returns null if this is a root folder.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_folder_id');
    }

    /**
     * Get all direct child folders.
     * 
     * Returns all folders that are direct children of this folder,
     * excluding nested descendants.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_folder_id');
    }

    /**
     * Get all active child folders.
     * 
     * Returns only active child folders, excluding deleted or inactive folders.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    /**
     * Get all files in this folder.
     * 
     * Returns all files directly contained within this folder,
     * excluding files in subfolders.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
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
     * Get all favorites for this folder
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * Get all activities for this folder
     */
    public function activities(): HasMany
    {
        return $this->hasMany(RecentActivity::class);
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
     * Get the full hierarchical path of the folder.
     * 
     * Constructs a breadcrumb-style path showing the complete folder hierarchy
     * from root to this folder (e.g., "Root / Documents / Projects / 2024").
     * 
     * @return string Full folder path with separators
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
     * Get all descendant folders recursively.
     * 
     * Returns a collection of all folders that are descendants of this folder,
     * including children, grandchildren, and all deeper levels.
     * 
     * @return \Illuminate\Support\Collection Collection of descendant folders
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
     * Check if this folder is a descendant of another folder.
     * 
     * Determines if this folder is nested within the specified folder's hierarchy.
     * Used for permission inheritance and access control validation.
     * 
     * @param Folder $folder The potential ancestor folder
     * @return bool True if this folder is a descendant
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
     * Get the total size of all files in this folder and subfolders.
     * 
     * Calculates the total storage size by recursively summing file sizes
     * from this folder and all its descendants. Used for storage analytics
     * and quota management.
     * 
     * @return int Total size in bytes
     */
    public function getTotalSizeAttribute(): int
    {
        $size = $this->files()->sum('file_size');
        
        foreach ($this->children as $child) {
            $size += $child->total_size;
        }
        
        return $size;
    }

    /**
     * Compute a simple relevance score for folders used in search results
     */
    public function getSearchRelevanceScore(string $searchTerm): float
    {
        $score = 0;
        $term = strtolower($searchTerm);

        if (stripos($this->name ?? '', $term) !== false) {
            $score += 10;
        }

        if (stripos($this->description ?? '', $term) !== false) {
            $score += 5;
        }

        if (strtolower($this->name ?? '') === $term) {
            $score += 5;
        }

        return $score;
    }
}
