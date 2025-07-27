<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class FilePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'folder_id',
        'assignable_type',
        'assignable_id',
        'permissions',
        'assigned_by',
        'is_inherited',
        'inherited_from_folder_id',
        'notes',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_inherited' => 'boolean',
    ];

    /**
     * Default permission structure
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
     * Permission level presets
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
