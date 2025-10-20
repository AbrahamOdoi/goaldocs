<?php

/**
 * FilePermission Model - Access Control Management for GoalDocs Enterprise System
 * 
 * This model represents the core permission system in GoalDocs, providing granular
 * access control for files and folders across users, positions, and departments.
 * 
 * Key Features:
 * - Granular permission system (view, download, edit, upload, delete, reshare, manage)
 * - Polymorphic assignment to users, positions, and departments
 * - Permission inheritance from parent folders
 * - Permission presets for common access patterns
 * - Audit trail with assignment tracking
 * - Flexible permission management with custom and preset options
 * 
 * Permission Types:
 * - view: Can see the file/folder in listings
 * - download: Can download files
 * - edit: Can modify file content
 * - upload: Can add new files to folders
 * - delete: Can remove files/folders
 * - reshare: Can share files with others
 * - manage: Can modify permissions (admin-level access)
 * 
 * Assignment Types:
 * - Direct assignment to users
 * - Assignment to positions (affects all users in that position)
 * - Assignment to departments (affects all users in that department)
 * - Inherited permissions from parent folders
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
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FilePermission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<int, string>
     */
    protected $fillable = [
        'file_id',              // File ID (null for folder permissions)
        'folder_id',            // Folder ID (null for file permissions)
        'assignable_type',      // Type of assignable entity (User, Position, Department)
        'assignable_id',        // ID of the assignable entity
        'permissions',          // JSON array of permission flags
        'assigned_by',          // User ID who assigned this permission
        'is_inherited',         // Whether permission is inherited from parent
        'inherited_from_folder_id', // Folder ID this was inherited from
        'notes',                // Optional notes about the permission
    ];

    /**
     * The attributes that should be cast.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',     // Cast permissions to array
        'is_inherited' => 'boolean',  // Cast inheritance flag to boolean
    ];

    /**
     * The accessors to append to the model's array form.
     * 
     * @var array<int, string>
     */
    protected $appends = [
        'permission_level',  // Human-readable permission level
        'resource',          // The file or folder this permission applies to
        'resource_type',     // Type of resource (file or folder)
    ];

    /**
     * Get the default permission structure with all permissions set to false.
     * 
     * Returns a standardized permission array that can be used as a starting
     * point for creating new permissions or as a fallback for missing permissions.
     * 
     * @return array Default permission structure
     */
    public static function getDefaultPermissions(): array
    {
        return [
            'view' => false,
            'download' => false,
            'edit' => false,
            'upload' => false,
            'delete' => false,
            'reshare' => false,
            'manage' => false,
        ];
    }

    /**
     * Get predefined permission level presets for common access patterns.
     * 
     * Returns a collection of permission presets that can be applied to users,
     * positions, or departments for standardized access control.
     * 
     * Preset Levels:
     * - view_only: Can only view files/folders
     * - read_download: Can view and download files
     * - contributor: Can view, download, edit, and upload files
     * - editor: Full file management except permission management
     * - full_access: Complete administrative access
     * 
     * @return array Permission presets with their configurations
     */
    public static function getPermissionPresets(): array
    {
        return [
            'view_only' => [
                'view' => true,
                'download' => false,
                'edit' => false,
                'upload' => false,
                'delete' => false,
                'reshare' => false,
                'manage' => false,
            ],
            'read_download' => [
                'view' => true,
                'download' => true,
                'edit' => false,
                'upload' => false,
                'delete' => false,
                'reshare' => false,
                'manage' => false,
            ],
            'contributor' => [
                'view' => true,
                'download' => true,
                'edit' => true,
                'upload' => true,
                'delete' => false,
                'reshare' => false,
                'manage' => false,
            ],
            'editor' => [
                'view' => true,
                'download' => true,
                'edit' => true,
                'upload' => true,
                'delete' => true,
                'reshare' => true,
                'manage' => false,
            ],
            'full_access' => [
                'view' => true,
                'download' => true,
                'edit' => true,
                'upload' => true,
                'delete' => true,
                'reshare' => true,
                'manage' => true,
            ],
        ];
    }

    /**
     * Get the file this permission applies to
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    /**
     * Get the folder this permission applies to
     */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    /**
     * Get the assignable entity (User, Position, Department)
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who assigned this permission
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the folder this permission was inherited from
     */
    public function inheritedFromFolder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'inherited_from_folder_id');
    }

    /**
     * Check if a specific permission is granted
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions[$permission] ?? false;
    }

    /**
     * Grant a specific permission
     */
    public function grantPermission(string $permission): void
    {
        $permissions = $this->permissions;
        $permissions[$permission] = true;
        $this->update(['permissions' => $permissions]);
    }

    /**
     * Revoke a specific permission
     */
    public function revokePermission(string $permission): void
    {
        $permissions = $this->permissions;
        $permissions[$permission] = false;
        $this->update(['permissions' => $permissions]);
    }

    /**
     * Set multiple permissions at once
     */
    public function setPermissions(array $permissions): void
    {
        $currentPermissions = $this->permissions;
        foreach ($permissions as $permission => $value) {
            $currentPermissions[$permission] = $value;
        }
        $this->update(['permissions' => $currentPermissions]);
    }

    /**
     * Apply a permission preset
     */
    public function applyPreset(string $preset): void
    {
        $presets = self::getPermissionPresets();
        if (isset($presets[$preset])) {
            $this->update(['permissions' => $presets[$preset]]);
        }
    }

    /**
     * Get human-readable permission level
     */
    public function getPermissionLevelAttribute(): string
    {
        $permissions = $this->permissions;
        $presets = self::getPermissionPresets();
        
        foreach ($presets as $presetName => $presetPermissions) {
            if ($permissions === $presetPermissions) {
                return ucwords(str_replace('_', ' ', $presetName));
            }
        }
        
        return 'Custom';
    }

    /**
     * Check if this is a resource (file or folder) permission
     */
    public function getResourceAttribute(): Model|null
    {
        return $this->file ?? $this->folder;
    }

    /**
     * Get the resource type (file or folder)
     */
    public function getResourceTypeAttribute(): string
    {
        return $this->file_id ? 'file' : 'folder';
    }

    /**
     * Scope for file permissions
     */
    public function scopeForFile($query, $fileId)
    {
        return $query->where('file_id', $fileId)->whereNull('folder_id');
    }

    /**
     * Scope for folder permissions
     */
    public function scopeForFolder($query, $folderId)
    {
        return $query->where('folder_id', $folderId)->whereNull('file_id');
    }

    /**
     * Scope for permissions assigned to a specific assignable entity
     */
    public function scopeForAssignable($query, $assignableType, $assignableId)
    {
        return $query->where('assignable_type', $assignableType)
                    ->where('assignable_id', $assignableId);
    }

    /**
     * Scope for inherited permissions
     */
    public function scopeInherited($query)
    {
        return $query->where('is_inherited', true);
    }

    /**
     * Scope for direct (non-inherited) permissions
     */
    public function scopeDirect($query)
    {
        return $query->where('is_inherited', false);
    }
}
