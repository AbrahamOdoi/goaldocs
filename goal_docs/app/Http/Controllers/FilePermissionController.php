<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\FilePermission;
use App\Models\User;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class FilePermissionController extends Controller
{
    /**
     * Get permissions for a specific resource
     */
    public function getPermissions(Request $request): JsonResponse
    {
        $request->validate([
            'resource_type' => 'required|string|in:file,folder',
            'resource_id' => 'required|integer'
        ]);

        try {
            $query = FilePermission::with('assignable');
            
            if ($request->resource_type === 'file') {
                $permissions = $query->where('file_id', $request->resource_id)->get();
            } else {
                $permissions = $query->where('folder_id', $request->resource_id)->get();
            }

            // Get assignable entities (users, positions, departments) - filtered by current user's organization
            $currentUser = Auth::user();
            $assignableEntities = [
                'users' => User::where('type', $currentUser->type)
                    ->where('type_name', $currentUser->type_name)
                    ->where('is_active', true)
                    ->get(['id', 'name', 'email']),
                'positions' => Position::whereHas('department', function($query) use ($currentUser) {
                    $query->where('user_type', $currentUser->type)
                          ->where('type', $currentUser->type_name);
                })
                ->where('is_active', true)
                ->get(['id', 'name', 'department_id']),
                'departments' => Department::where('user_type', $currentUser->type)
                    ->where('type', $currentUser->type_name)
                    ->where('is_active', true)
                    ->get(['id', 'name', 'type'])
            ];

            return response()->json([
                'success' => true,
                'permissions' => $permissions,
                'assignable_entities' => $assignableEntities
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permission to a resource
     */
    public function assignPermission(Request $request): JsonResponse
    {
        $request->validate([
            'resource_type' => 'required|string|in:file,folder',
            'resource_id' => 'required|integer',
            'assignable_type' => 'required|string|in:App\Models\User,App\Models\Position,App\Models\Department',
            'assignable_id' => 'required|integer',
            'permissions' => 'required|array'
        ]);

        try {
            // Build the query based on resource type
            $query = FilePermission::where('assignable_type', $request->assignable_type)
                ->where('assignable_id', $request->assignable_id);
            
            if ($request->resource_type === 'file') {
                $query->where('file_id', $request->resource_id);
            } else {
                $query->where('folder_id', $request->resource_id);
            }
            
            $existingPermission = $query->first();

            if ($existingPermission) {
                // Update existing permission
                $existingPermission->update([
                    'permissions' => $request->permissions
                ]);
            } else {
                // Create new permission
                $permissionData = [
                    'assignable_type' => $request->assignable_type,
                    'assignable_id' => $request->assignable_id,
                    'permissions' => $request->permissions,
                    'assigned_by' => Auth::id()
                ];
                
                if ($request->resource_type === 'file') {
                    $permissionData['file_id'] = $request->resource_id;
                } else {
                    $permissionData['folder_id'] = $request->resource_id;
                }
                
                FilePermission::create($permissionData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Permission assigned successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to assign permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove permission from a resource
     */
    public function removePermission(Request $request): JsonResponse
    {
        $request->validate([
            'assignable_type' => 'required|string',
            'assignable_id' => 'required|integer'
        ]);

        try {
            FilePermission::where('assignable_type', $request->assignable_type)
                ->where('assignable_id', $request->assignable_id)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Permission removed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to remove permission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user's permissions
     */
    public function myPermissions(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $permissions = FilePermission::where('assignable_type', User::class)
                ->where('assignable_id', $user->id)
                ->get();

            return response()->json([
                'success' => true,
                'permissions' => $permissions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load permissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get preset permission templates
     */
    public function getPresetPermissions(Request $request): JsonResponse
    {
        $presets = [
            'read_only' => [
                'view' => true,
                'download' => true,
                'edit' => false,
                'delete' => false,
                'share' => false,
                'admin' => false
            ],
            'editor' => [
                'view' => true,
                'download' => true,
                'edit' => true,
                'delete' => false,
                'share' => true,
                'admin' => false
            ],
            'admin' => [
                'view' => true,
                'download' => true,
                'edit' => true,
                'delete' => true,
                'share' => true,
                'admin' => true
            ],
            'full_access' => [
                'view' => true,
                'download' => true,
                'edit' => true,
                'delete' => true,
                'share' => true,
                'admin' => true
            ]
        ];

        return response()->json([
            'success' => true,
            'presets' => $presets
        ]);
    }

    /**
     * Bulk assign permissions to multiple resources
     */
    public function bulkAssignPermissions(Request $request): JsonResponse
    {
        $request->validate([
            'resource_ids' => 'required|array|min:1',
            'resource_type' => 'required|string|in:file,folder',
            'assignable_type' => 'required|string|in:user,position,department',
            'assignable_ids' => 'required|array|min:1',
            'permissions' => 'required|array',
        ]);

        try {
            $permissionService = app(\App\Services\PermissionService::class);
            
            $assignableData = [];
            foreach ($request->assignable_ids as $assignableId) {
                $assignableData[] = [
                    'type' => 'App\\Models\\' . ucfirst($request->assignable_type),
                    'id' => $assignableId,
                    'assigned_by' => Auth::id(),
                ];
            }

            $success = $permissionService->bulkAssignPermissions(
                $assignableData,
                $request->resource_ids,
                $request->permissions,
                $request->resource_type
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permissions assigned successfully',
                    'assigned_count' => count($request->resource_ids) * count($request->assignable_ids)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to assign permissions'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Copy permissions from one resource to another
     */
    public function copyPermissions(Request $request): JsonResponse
    {
        $request->validate([
            'source_resource_type' => 'required|string|in:file,folder',
            'source_resource_id' => 'required|integer',
            'target_resource_type' => 'required|string|in:file,folder',
            'target_resource_id' => 'required|integer',
            'exclude_assignables' => 'nullable|array',
        ]);

        try {
            $permissionService = app(\App\Services\PermissionService::class);
            
            // Get source and target resources
            $sourceResource = $this->getResource($request->source_resource_type, $request->source_resource_id);
            $targetResource = $this->getResource($request->target_resource_type, $request->target_resource_id);

            if (!$sourceResource || !$targetResource) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found'
                ], 404);
            }

            $success = $permissionService->copyPermissions(
                $sourceResource,
                $targetResource,
                $request->exclude_assignables ?? []
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permissions copied successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to copy permissions'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get access report for a resource
     */
    public function getAccessReport($resourceId): JsonResponse
    {
        try {
            $permissionService = app(\App\Services\PermissionService::class);
            
            // Determine resource type and get the resource
            $resource = \App\Models\File::find($resourceId) ?? \App\Models\Folder::find($resourceId);
            
            if (!$resource) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found'
                ], 404);
            }

            $report = $permissionService->getAccessReport($resource);

            return response()->json([
                'success' => true,
                'report' => $report
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show bulk assign permissions page
     */
    public function showBulkAssign()
    {
        return view('permissions.bulk-assign');
    }

    /**
     * Get assignables for API
     */
    public function getAssignables(string $type): JsonResponse
    {
        try {
            $currentUser = Auth::user();
            $assignables = [];

            switch ($type) {
                case 'user':
                    $assignables = User::where('type', $currentUser->type)
                        ->where('type_name', $currentUser->type_name)
                        ->where('is_active', true)
                        ->get(['id', 'name', 'email'])
                        ->map(function($user) {
                            return [
                                'id' => $user->id,
                                'name' => $user->name,
                                'type' => 'User',
                                'description' => $user->email
                            ];
                        });
                    break;

                case 'position':
                    $assignables = Position::whereHas('department', function($query) use ($currentUser) {
                        $query->where('user_type', $currentUser->type)
                              ->where('type', $currentUser->type_name);
                    })
                    ->where('is_active', true)
                    ->with('department')
                    ->get()
                    ->map(function($position) {
                        return [
                            'id' => $position->id,
                            'name' => $position->name,
                            'type' => 'Position',
                            'description' => $position->department->name . ' • ' . ucfirst($position->level)
                        ];
                    });
                    break;

                case 'department':
                    $assignables = Department::where('user_type', $currentUser->type)
                        ->where('type', $currentUser->type_name)
                        ->where('is_active', true)
                        ->get(['id', 'name', 'description'])
                        ->map(function($department) {
                            return [
                                'id' => $department->id,
                                'name' => $department->name,
                                'type' => 'Department',
                                'description' => $department->description ?? 'Department'
                            ];
                        });
                    break;
            }

            return response()->json($assignables);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load assignables: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get resources for API
     */
    public function getResources(string $type, Request $request): JsonResponse
    {
        try {
            $currentUser = Auth::user();
            $search = $request->get('search', '');
            $resources = [];

            switch ($type) {
                case 'file':
                    $query = \App\Models\File::where('user_type', $currentUser->type)
                        ->where('user_type_name', $currentUser->type_name)
                        ->where('is_active', true);
                    
                    if ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    }
                    
                    $resources = $query->get(['id', 'name', 'file_size', 'created_at'])
                        ->map(function($file) {
                            return [
                                'id' => $file->id,
                                'name' => $file->name,
                                'type' => 'File',
                                'path' => '/',
                                'permission_count' => \App\Models\FilePermission::where('file_id', $file->id)->count()
                            ];
                        });
                    break;

                case 'folder':
                    $query = \App\Models\Folder::where('user_type', $currentUser->type)
                        ->where('type_name', $currentUser->type_name)
                        ->where('is_active', true);
                    
                    if ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    }
                    
                    $resources = $query->get(['id', 'name', 'created_at'])
                        ->map(function($folder) {
                            return [
                                'id' => $folder->id,
                                'name' => $folder->name,
                                'type' => 'Folder',
                                'path' => '/',
                                'permission_count' => \App\Models\FilePermission::where('folder_id', $folder->id)->count()
                            ];
                        });
                    break;
            }

            return response()->json($resources);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load resources: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get resource by type and ID
     */
    private function getResource(string $type, int $id)
    {
        switch ($type) {
            case 'file':
                return \App\Models\File::find($id);
            case 'folder':
                return \App\Models\Folder::find($id);
            default:
                return null;
        }
    }
}
