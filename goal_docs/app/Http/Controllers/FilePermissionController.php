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

            // Get assignable entities (users, positions, departments)
            $assignableEntities = [
                'users' => User::where('is_active', true)->get(['id', 'name', 'email']),
                'positions' => Position::where('is_active', true)->get(['id', 'name', 'department_id']),
                'departments' => Department::where('is_active', true)->get(['id', 'name', 'type'])
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
}
