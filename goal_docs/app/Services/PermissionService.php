<?php

namespace App\Services;

use App\Models\File;
use App\Models\Folder;
use App\Models\User;
use App\Models\Position;
use App\Models\Department;
use App\Models\FilePermission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PermissionService
{
    /**
     * Check if a user has a specific permission on a file or folder
     */
    public function userHasPermission(User $user, $resource, string $permission): bool
    {
        // Check direct permissions
        if ($this->hasDirectPermission($user, $resource, $permission)) {
            return true;
        }

        // Check permissions through positions
        foreach ($user->positions as $position) {
            if ($this->hasDirectPermission($position, $resource, $permission)) {
                return true;
            }
        }

        // Check permissions through departments
        foreach ($user->positions as $position) {
            if ($position->department && $this->hasDirectPermission($position->department, $resource, $permission)) {
                return true;
            }
        }

        // Check inherited permissions (if resource is a file or subfolder)
        if ($this->hasInheritedPermission($user, $resource, $permission)) {
            return true;
        }

        return false;
    }

    /**
     * Check direct permission assignment for an assignable entity
     */
    private function hasDirectPermission($assignable, $resource, string $permission): bool
    {
        $query = FilePermission::forAssignable(get_class($assignable), $assignable->id);

        if ($resource instanceof File) {
            $query->forFile($resource->id);
        } elseif ($resource instanceof Folder) {
            $query->forFolder($resource->id);
        } else {
            return false;
        }

        $filePermission = $query->first();
        
        return $filePermission && $filePermission->hasPermission($permission);
    }

    /**
     * Check inherited permissions from parent folders
     */
    private function hasInheritedPermission(User $user, $resource, string $permission): bool
    {
        $folder = null;
        
        if ($resource instanceof File) {
            $folder = $resource->folder;
        } elseif ($resource instanceof Folder) {
            $folder = $resource->parent;
        }

        while ($folder) {
            if ($this->userHasPermission($user, $folder, $permission)) {
                return true;
            }
            $folder = $folder->parent;
        }

        return false;
    }

    /**
     * Assign permissions to a resource
     */
    public function assignPermission($resource, $assignable, array $permissions, User $assignedBy, ?string $notes = null): FilePermission
    {
        $data = [
            'assignable_type' => get_class($assignable),
            'assignable_id' => $assignable->id,
            'permissions' => array_merge(FilePermission::getDefaultPermissions(), $permissions),
            'assigned_by' => $assignedBy->id,
            'notes' => $notes,
        ];

        if ($resource instanceof File) {
            $data['file_id'] = $resource->id;
        } elseif ($resource instanceof Folder) {
            $data['folder_id'] = $resource->id;
        }

        // Check if permission already exists and update, or create new
        $existingPermission = FilePermission::query()
            ->where('assignable_type', $data['assignable_type'])
            ->where('assignable_id', $data['assignable_id']);

        if ($resource instanceof File) {
            $existingPermission->where('file_id', $data['file_id']);
        } else {
            $existingPermission->where('folder_id', $data['folder_id']);
        }

        $existingPermission = $existingPermission->first();

        if ($existingPermission) {
            $existingPermission->update($data);
            return $existingPermission;
        }

        return FilePermission::create($data);
    }

    /**
     * Remove permission assignment
     */
    public function removePermission($resource, $assignable): bool
    {
        $query = FilePermission::forAssignable(get_class($assignable), $assignable->id);

        if ($resource instanceof File) {
            $query->forFile($resource->id);
        } elseif ($resource instanceof Folder) {
            $query->forFolder($resource->id);
        }

        return $query->delete() > 0;
    }

    /**
     * Get all permissions for a resource
     */
    public function getResourcePermissions($resource): Collection
    {
        if ($resource instanceof File) {
            return $resource->permissions()->with(['assignable', 'assignedBy'])->get();
        } elseif ($resource instanceof Folder) {
            return $resource->permissions()->with(['assignable', 'assignedBy'])->get();
        }

        return collect();
    }

    /**
     * Inherit permissions from parent folder to children
     */
    public function inheritPermissionsToChildren(Folder $folder): void
    {
        $parentPermissions = $folder->permissions()->direct()->get();

        foreach ($parentPermissions as $permission) {
            // Inherit to subfolders
            foreach ($folder->children as $subfolder) {
                $this->createInheritedPermission($subfolder, $permission, $folder);
                // Recursively inherit to deeper levels
                $this->inheritPermissionsToChildren($subfolder);
            }

            // Inherit to files in this folder
            foreach ($folder->files as $file) {
                $this->createInheritedPermission($file, $permission, $folder);
            }
        }
    }

    /**
     * Create an inherited permission
     */
    private function createInheritedPermission($resource, FilePermission $sourcePermission, Folder $sourceFolder): void
    {
        $data = [
            'assignable_type' => $sourcePermission->assignable_type,
            'assignable_id' => $sourcePermission->assignable_id,
            'permissions' => $sourcePermission->permissions,
            'assigned_by' => $sourcePermission->assigned_by,
            'is_inherited' => true,
            'inherited_from_folder_id' => $sourceFolder->id,
            'notes' => "Inherited from folder: {$sourceFolder->name}",
        ];

        if ($resource instanceof File) {
            $data['file_id'] = $resource->id;
        } elseif ($resource instanceof Folder) {
            $data['folder_id'] = $resource->id;
        }

        // Check if inherited permission already exists
        $existingInherited = FilePermission::query()
            ->where('assignable_type', $data['assignable_type'])
            ->where('assignable_id', $data['assignable_id'])
            ->where('is_inherited', true)
            ->where('inherited_from_folder_id', $sourceFolder->id);

        if ($resource instanceof File) {
            $existingInherited->where('file_id', $data['file_id']);
        } else {
            $existingInherited->where('folder_id', $data['folder_id']);
        }

        if (!$existingInherited->exists()) {
            FilePermission::create($data);
        }
    }

    /**
     * Remove inherited permissions when parent permission is removed
     */
    public function removeInheritedPermissions(Folder $folder, $assignableType, $assignableId): void
    {
        // Remove from direct children (folders and files)
        FilePermission::query()
            ->where('assignable_type', $assignableType)
            ->where('assignable_id', $assignableId)
            ->where('is_inherited', true)
            ->where('inherited_from_folder_id', $folder->id)
            ->delete();

        // Recursively remove from subfolders
        foreach ($folder->children as $subfolder) {
            $this->removeInheritedPermissions($subfolder, $assignableType, $assignableId);
        }
    }

    /**
     * Get all assignable entities for a specific user's organization
     */
    public function getAssignableEntities(User $currentUser): array
    {
        $entities = [
            'users' => User::where('type', $currentUser->type)
                          ->where('type_name', $currentUser->type_name)
                          ->get(),
        ];

        if ($currentUser->type !== 'individual') {
            // Get departments/roles with proper naming
            $departments = Department::forUserType($currentUser->type)
                                   ->where('type', $currentUser->type_name)
                                   ->active()
                                   ->get();
            
            // Use appropriate key based on user type
            $departmentKey = $this->getDepartmentKey($currentUser->type);
            $entities[$departmentKey] = $departments;
            
            $entities['positions'] = Position::whereHas('department', function($query) use ($currentUser) {
                $query->where('user_type', $currentUser->type)
                      ->where('type', $currentUser->type_name);
            })->active()->get();
        }

        return $entities;
    }

    /**
     * Get the appropriate key for departments based on user type
     */
    private function getDepartmentKey(string $userType): string
    {
        $keyMapping = [
            'family' => 'roles',
            'organisation' => 'departments',
            'government' => 'agencies',
            'social_group' => 'groups',
            'professional_group' => 'divisions',
            'educational_institution' => 'departments',
            'non_profit' => 'departments',
            'individual' => 'categories', // Individual users don't typically have hierarchies, but include for completeness
        ];

        return $keyMapping[$userType] ?? 'departments';
    }

    /**
     * Check if user can manage permissions (is admin or has manage permission)
     */
    public function canManagePermissions(User $user, $resource): bool
    {
        if ($user->is_admin) {
            return true;
        }

        return $this->userHasPermission($user, $resource, 'manage');
    }

    /**
     * Get effective permissions for a user on a resource (combining all sources)
     */
    public function getEffectivePermissions(User $user, $resource): array
    {
        $effectivePermissions = FilePermission::getDefaultPermissions();

        // Check direct user permissions
        $this->mergePermissions($effectivePermissions, $this->getDirectPermissions($user, $resource));

        // Check position permissions
        foreach ($user->positions as $position) {
            $this->mergePermissions($effectivePermissions, $this->getDirectPermissions($position, $resource));
        }

        // Check department permissions
        foreach ($user->positions as $position) {
            if ($position->department) {
                $this->mergePermissions($effectivePermissions, $this->getDirectPermissions($position->department, $resource));
            }
        }

        // Check inherited permissions
        $this->mergePermissions($effectivePermissions, $this->getInheritedPermissions($user, $resource));

        return $effectivePermissions;
    }

    /**
     * Bulk assign permissions to multiple resources
     */
    public function bulkAssignPermissions(array $assignableData, array $resourceIds, array $permissions, string $resourceType = 'folder'): bool
    {
        try {
            DB::beginTransaction();

            foreach ($assignableData as $assignable) {
                foreach ($resourceIds as $resourceId) {
                    $this->assignPermissionInternal(
                        $assignable['type'],
                        $assignable['id'],
                        $resourceId,
                        $resourceType,
                        $permissions,
                        $assignable['assigned_by'] ?? null
                    );
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk permission assignment failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Copy permissions from one resource to another
     */
    public function copyPermissions($sourceResource, $targetResource, array $excludeAssignables = []): bool
    {
        try {
            DB::beginTransaction();

            $sourcePermissions = FilePermission::where(function($query) use ($sourceResource) {
                if ($sourceResource instanceof File) {
                    $query->where('file_id', $sourceResource->id);
                } elseif ($sourceResource instanceof Folder) {
                    $query->where('folder_id', $sourceResource->id);
                }
            })->get();

            foreach ($sourcePermissions as $permission) {
                // Skip if assignable is in exclude list
                if (in_array($permission->assignable_type . ':' . $permission->assignable_id, $excludeAssignables)) {
                    continue;
                }

                $newPermission = $permission->replicate();
                $newPermission->file_id = $targetResource instanceof File ? $targetResource->id : null;
                $newPermission->folder_id = $targetResource instanceof Folder ? $targetResource->id : null;
                $newPermission->is_inherited = false;
                $newPermission->inherited_from_folder_id = null;
                $newPermission->save();
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Permission copy failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Apply permission template to assignable
     */
    public function applyPermissionTemplate($assignable, $resource, string $templateName): bool
    {
        $templates = $this->getPermissionTemplates();
        
        if (!isset($templates[$templateName])) {
            return false;
        }

        $permissions = $templates[$templateName];
        return $this->assignPermissionInternal(
            get_class($assignable),
            $assignable->id,
            $resource->id,
            $resource instanceof File ? 'file' : 'folder',
            $permissions
        );
    }

    /**
     * Get available permission templates
     */
    public function getPermissionTemplates(): array
    {
        return [
            'viewer' => [
                'view' => true,
                'download' => true,
                'edit' => false,
                'upload' => false,
                'delete' => false,
                'reshare' => false,
                'manage' => false,
            ],
            'editor' => [
                'view' => true,
                'download' => true,
                'edit' => true,
                'upload' => true,
                'delete' => false,
                'reshare' => true,
                'manage' => false,
            ],
            'manager' => [
                'view' => true,
                'download' => true,
                'edit' => true,
                'upload' => true,
                'delete' => true,
                'reshare' => true,
                'manage' => false,
            ],
            'admin' => [
                'view' => true,
                'download' => true,
                'edit' => true,
                'upload' => true,
                'delete' => true,
                'reshare' => true,
                'manage' => true,
            ],
            'read_only' => [
                'view' => true,
                'download' => false,
                'edit' => false,
                'upload' => false,
                'delete' => false,
                'reshare' => false,
                'manage' => false,
            ],
        ];
    }

    /**
     * Resolve permission conflicts when user has multiple positions
     */
    public function resolvePermissionConflicts(User $user, $resource): array
    {
        $conflicts = [];
        $userPositions = $user->activePositions;

        if ($userPositions->count() <= 1) {
            return $conflicts;
        }

        $positionPermissions = [];
        foreach ($userPositions as $position) {
            $permissions = $this->getDirectPermissions($position, $resource);
            $positionPermissions[$position->id] = [
                'position' => $position,
                'permissions' => $permissions,
            ];
        }

        // Check for conflicts in each permission type
        $permissionTypes = ['view', 'download', 'edit', 'upload', 'delete', 'reshare', 'manage'];
        
        foreach ($permissionTypes as $permissionType) {
            $values = [];
            foreach ($positionPermissions as $data) {
                $values[] = $data['permissions'][$permissionType] ?? false;
            }
            
            // If not all values are the same, there's a conflict
            if (count(array_unique($values)) > 1) {
                $conflicts[$permissionType] = $positionPermissions;
            }
        }

        return $conflicts;
    }

    /**
     * Get access report for a resource
     */
    public function getAccessReport($resource): array
    {
        $permissions = FilePermission::where(function($query) use ($resource) {
            if ($resource instanceof File) {
                $query->where('file_id', $resource->id);
            } elseif ($resource instanceof Folder) {
                $query->where('folder_id', $resource->id);
            }
        })->with(['assignable', 'assignedBy'])->get();

        $report = [
            'resource' => [
                'id' => $resource->id,
                'name' => $resource->name,
                'type' => $resource instanceof File ? 'file' : 'folder',
            ],
            'permissions' => [],
            'summary' => [
                'total_assignments' => $permissions->count(),
                'by_type' => [
                    'users' => 0,
                    'positions' => 0,
                    'departments' => 0,
                ],
                'by_permission' => [
                    'view' => 0,
                    'download' => 0,
                    'edit' => 0,
                    'upload' => 0,
                    'delete' => 0,
                    'reshare' => 0,
                    'manage' => 0,
                ],
            ],
        ];

        foreach ($permissions as $permission) {
            $assignableType = class_basename($permission->assignable_type);
            $assignableName = $permission->assignable->name ?? 'Unknown';
            
            $report['permissions'][] = [
                'id' => $permission->id,
                'assignable_type' => $assignableType,
                'assignable_name' => $assignableName,
                'permissions' => $permission->permissions,
                'assigned_by' => $permission->assignedBy->name ?? 'System',
                'assigned_at' => $permission->created_at,
                'is_inherited' => $permission->is_inherited,
            ];

            // Update summary
            $report['summary']['by_type'][strtolower($assignableType) . 's']++;
            
            foreach ($permission->permissions as $perm => $value) {
                if ($value) {
                    $report['summary']['by_permission'][$perm]++;
                }
            }
        }

        return $report;
    }

    /**
     * Assign permission to an assignable entity (internal method)
     */
    private function assignPermissionInternal(string $assignableType, int $assignableId, int $resourceId, string $resourceType, array $permissions, ?int $assignedBy = null): bool
    {
        try {
            $permissionData = [
                'assignable_type' => $assignableType,
                'assignable_id' => $assignableId,
                'permissions' => $permissions,
                'assigned_by' => $assignedBy ?? Auth::id(),
                'is_inherited' => false,
            ];

            if ($resourceType === 'file') {
                $permissionData['file_id'] = $resourceId;
            } else {
                $permissionData['folder_id'] = $resourceId;
            }

            FilePermission::updateOrCreate(
                [
                    'assignable_type' => $assignableType,
                    'assignable_id' => $assignableId,
                    $resourceType . '_id' => $resourceId,
                ],
                $permissionData
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Permission assignment failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get direct permissions for an assignable entity on a resource
     */
    private function getDirectPermissions($assignable, $resource): array
    {
        $query = FilePermission::forAssignable(get_class($assignable), $assignable->id);

        if ($resource instanceof File) {
            $query->forFile($resource->id);
        } elseif ($resource instanceof Folder) {
            $query->forFolder($resource->id);
        }

        $permission = $query->first();
        return $permission ? $permission->permissions : FilePermission::getDefaultPermissions();
    }

    /**
     * Get inherited permissions for a user on a resource
     */
    private function getInheritedPermissions(User $user, $resource): array
    {
        $inheritedPermissions = FilePermission::getDefaultPermissions();
        
        $folder = null;
        if ($resource instanceof File) {
            $folder = $resource->folder;
        } elseif ($resource instanceof Folder) {
            $folder = $resource->parent;
        }

        while ($folder) {
            $folderPermissions = $this->getEffectivePermissions($user, $folder);
            $this->mergePermissions($inheritedPermissions, $folderPermissions);
            $folder = $folder->parent;
        }

        return $inheritedPermissions;
    }

    /**
     * Merge permissions (OR operation - if any source grants permission, it's granted)
     */
    private function mergePermissions(array &$target, array $source): void
    {
        foreach ($source as $permission => $value) {
            $target[$permission] = $target[$permission] || $value;
        }
    }
} 