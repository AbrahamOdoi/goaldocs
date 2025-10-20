<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HierarchyController extends Controller
{
    private function checkAdminAccess()
    {
        if (!Auth::user()->is_admin) {
            abort(403, 'Access denied. Only administrators can manage organizational structure.');
        }
    }

    public function index()
    {
        $this->checkAdminAccess();
        $user = Auth::user();
        $userType = $user->type ?? 'organisation';
        
        $departments = Department::forUserType($userType)
            ->where('type', $user->type_name)
            ->active()
            ->with(['positions' => function($query) {
                $query->active()->withCount('activeUsers');
            }])
            ->withCount('activePositions')
            ->get();

        $displayName = $this->getDisplayName($userType);

        return view('hierarchy.index', compact('departments', 'userType', 'displayName'));
    }

    public function createDepartment()
    {
        $this->checkAdminAccess();
        $user = Auth::user();
        $userType = $user->type ?? 'organisation';
        $displayName = $this->getDisplayName($userType);

        return view('hierarchy.departments.create', compact('userType', 'displayName'));
    }

    public function storeDepartment(Request $request)
    {
        $this->checkAdminAccess();
        $user = Auth::user();
        $userType = $user->type ?? 'organisation';

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        Department::create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $user->type_name,
            'user_type' => $userType,
            'color' => $request->color,
            'created_by' => $user->id,
        ]);

        // Clear any cached department data
        $this->clearDepartmentCache($user);

        return redirect()->route('hierarchy.index')
            ->with('success', ucfirst($this->getDisplayName($userType)) . ' created successfully!')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
    }

    public function editDepartment(Department $department)
    {
        $user = Auth::user();
        $userType = $user->type ?? 'organisation';
        $displayName = $this->getDisplayName($userType);

        return view('hierarchy.departments.edit', compact('department', 'userType', 'displayName'));
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
        ]);

        $department->update([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color,
        ]);

        return redirect()->route('hierarchy.index')
            ->with('success', ucfirst($this->getDisplayName($department->user_type)) . ' updated successfully!')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
    }

    public function createPosition(Department $department)
    {
        $user = Auth::user();
        $userType = $user->type ?? 'organisation';
        $displayName = $this->getDisplayName($userType);

        // Debug: Check if variables are being passed correctly
        Log::info('Creating position view', [
            'department_id' => $department->id,
            'department_name' => $department->name,
            'user_type' => $userType,
            'display_name' => $displayName
        ]);

        return view('hierarchy.positions.create', compact('department', 'userType', 'displayName'));
    }

    public function storePosition(Request $request, Department $department)
    {
        $user = Auth::user();
        $validLevels = $this->getValidLevels($user->type ?? 'organisation');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|string|in:' . implode(',', $validLevels),
        ]);

        Position::create([
            'name' => $request->name,
            'description' => $request->description,
            'department_id' => $department->id,
            'level' => $request->level,
            'created_by' => $user->id,
        ]);

        // Clear any cached department data
        $this->clearDepartmentCache($user);

        return redirect()->route('hierarchy.index')
            ->with('success', 'Position created successfully!')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
    }

    public function editPosition(Position $position)
    {
        $user = Auth::user();
        $userType = $user->type ?? 'organisation';
        $displayName = $this->getDisplayName($userType);

        return view('hierarchy.positions.edit', compact('position', 'userType', 'displayName'));
    }

    public function updatePosition(Request $request, Position $position)
    {
        $user = Auth::user();
        $validLevels = $this->getValidLevels($user->type ?? 'organisation');
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|string|in:' . implode(',', $validLevels),
        ]);

        $position->update([
            'name' => $request->name,
            'description' => $request->description,
            'level' => $request->level,
        ]);

        return redirect()->route('hierarchy.index')
            ->with('success', 'Position updated successfully!')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);
    }

    public function assignPosition(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'position_id' => 'required|exists:positions,id',
            'is_primary' => 'boolean',
            'start_date' => 'nullable|date',
        ]);

        $currentUser = Auth::user();
        $user = User::where('id', $request->user_id)
            ->where('type', $currentUser->type)
            ->where('type_name', $currentUser->type_name)
            ->firstOrFail();
        $position = Position::whereHas('department', function($query) use ($currentUser) {
            $query->where('user_type', $currentUser->type)
                  ->where('type', $currentUser->type_name);
        })->findOrFail($request->position_id);

        // If this is a primary position, remove primary from other positions
        if ($request->is_primary) {
            $user->positions()->updateExistingPivot($user->positions->pluck('id'), ['is_primary' => false]);
        }

        $user->positions()->syncWithoutDetaching([
            $position->id => [
                'is_primary' => $request->is_primary ?? false,
                'start_date' => $request->start_date,
                'is_active' => true,
            ]
        ]);

        return response()->json(['success' => true, 'message' => 'Position assigned successfully!']);
    }

    public function removePosition(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'position_id' => 'required|exists:positions,id',
        ]);

        $currentUser = Auth::user();
        $user = User::where('id', $request->user_id)
            ->where('type', $currentUser->type)
            ->where('type_name', $currentUser->type_name)
            ->firstOrFail();
        
        // Verify position belongs to same organization before removing
        $position = Position::whereHas('department', function($query) use ($currentUser) {
            $query->where('user_type', $currentUser->type)
                  ->where('type', $currentUser->type_name);
        })->findOrFail($request->position_id);
        
        $user->positions()->detach($request->position_id);

        return response()->json(['success' => true, 'message' => 'Position removed successfully!']);
    }

    /**
     * Assign multiple positions to a user
     */
    public function assignMultiplePositions(Request $request)
    {
        $this->checkAdminAccess();
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'positions' => 'required|array|min:1',
            'positions.*.position_id' => 'required|exists:positions,id',
            'positions.*.is_primary' => 'boolean',
            'positions.*.start_date' => 'nullable|date',
        ]);

        $currentUser = Auth::user();
        $user = User::where('id', $request->user_id)
            ->where('type', $currentUser->type)
            ->where('type_name', $currentUser->type_name)
            ->firstOrFail();

        $positionData = [];
        $hasPrimary = false;

        foreach ($request->positions as $position) {
            // Verify position belongs to same organization
            $positionModel = Position::whereHas('department', function($query) use ($currentUser) {
                $query->where('user_type', $currentUser->type)
                      ->where('type', $currentUser->type_name);
            })->findOrFail($position['position_id']);

            $positionData[$position['position_id']] = [
                'is_primary' => $position['is_primary'] ?? false,
                'start_date' => $position['start_date'] ?? now(),
                'is_active' => true,
            ];

            if ($position['is_primary'] ?? false) {
                $hasPrimary = true;
            }
        }

        // If no primary position specified, make the first one primary
        if (!$hasPrimary && !empty($positionData)) {
            $firstPositionId = array_keys($positionData)[0];
            $positionData[$firstPositionId]['is_primary'] = true;
        }

        // If setting a new primary, remove primary from existing positions
        if ($hasPrimary) {
            $user->positions()->updateExistingPivot($user->positions->pluck('id'), ['is_primary' => false]);
        }

        $user->positions()->syncWithoutDetaching($positionData);

        return response()->json(['success' => true, 'message' => 'Positions assigned successfully!']);
    }

    /**
     * Set primary position for a user
     */
    public function setPrimaryPosition(Request $request)
    {
        $this->checkAdminAccess();
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'position_id' => 'required|exists:positions,id',
        ]);

        $currentUser = Auth::user();
        $user = User::where('id', $request->user_id)
            ->where('type', $currentUser->type)
            ->where('type_name', $currentUser->type_name)
            ->firstOrFail();

        $position = Position::whereHas('department', function($query) use ($currentUser) {
            $query->where('user_type', $currentUser->type)
                  ->where('type', $currentUser->type_name);
        })->findOrFail($request->position_id);

        // Verify user is assigned to this position
        if (!$user->positions()->where('position_id', $position->id)->exists()) {
            return response()->json(['error' => 'User is not assigned to this position'], 400);
        }

        // Remove primary from all positions
        $user->positions()->updateExistingPivot($user->positions->pluck('id'), ['is_primary' => false]);

        // Set new primary
        $user->positions()->updateExistingPivot($position->id, ['is_primary' => true]);

        return response()->json(['success' => true, 'message' => 'Primary position updated successfully!']);
    }

    /**
     * Get all positions for a user with primary flag
     */
    public function getUserPositions(User $user)
    {
        $this->checkAdminAccess();
        
        $currentUser = Auth::user();
        
        // Verify user belongs to same organization
        if ($user->type !== $currentUser->type || $user->type_name !== $currentUser->type_name) {
            abort(403, 'Access denied');
        }

        $positions = $user->positions()
            ->with(['department'])
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'department' => $position->department->name,
                    'level' => $position->level,
                    'is_primary' => $position->pivot->is_primary,
                    'start_date' => $position->pivot->start_date,
                    'is_active' => $position->pivot->is_active,
                ];
            });

        return response()->json(['positions' => $positions]);
    }

    /**
     * Get position history for a user
     */
    public function getPositionHistory(User $user)
    {
        $this->checkAdminAccess();
        
        $currentUser = Auth::user();
        
        // Verify user belongs to same organization
        if ($user->type !== $currentUser->type || $user->type_name !== $currentUser->type_name) {
            abort(403, 'Access denied');
        }

        $history = $user->positions()
            ->with(['department'])
            ->withPivot(['start_date', 'end_date', 'is_primary', 'is_active', 'created_at', 'updated_at'])
            ->orderBy('pivot_created_at', 'desc')
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'department' => $position->department->name,
                    'level' => $position->level,
                    'is_primary' => $position->pivot->is_primary,
                    'start_date' => $position->pivot->start_date,
                    'end_date' => $position->pivot->end_date,
                    'is_active' => $position->pivot->is_active,
                    'assigned_at' => $position->pivot->created_at,
                    'updated_at' => $position->pivot->updated_at,
                ];
            });

        return response()->json(['history' => $history]);
    }

    private function getDisplayName($userType)
    {
        $typeLabels = [
            'organisation' => 'Department',
            'family' => 'Role',
            'government' => 'Agency',
            'social_group' => 'Group',
            'professional_group' => 'Division',
            'educational_institution' => 'Department',
            'non_profit' => 'Department',
            'individual' => 'Category',
        ];

        return $typeLabels[$userType] ?? 'Department';
    }

    /**
     * Clear department-related cache
     */
    private function clearDepartmentCache($user)
    {
        try {
            // Clear any cached department data for this user
            $cacheKeys = [
                "departments_{$user->id}",
                "departments_{$user->type}_{$user->type_name}",
                "hierarchy_{$user->id}",
                "hierarchy_{$user->type}_{$user->type_name}",
            ];
            
            foreach ($cacheKeys as $key) {
                \Illuminate\Support\Facades\Cache::forget($key);
            }
            
            Log::info('Department cache cleared for user: ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Failed to clear department cache: ' . $e->getMessage());
        }
    }

    private function getTypeForUserType($userType)
    {
        $typeMapping = [
            'organisation' => 'department',
            'family' => 'role',
            'government' => 'agency',
            'social_group' => 'group',
            'professional_group' => 'division',
            'educational_institution' => 'department',
            'non_profit' => 'department',
            'individual' => 'category',
        ];

        return $typeMapping[$userType] ?? 'department';
    }

    private function getValidLevels($userType)
    {
        $levelMappings = [
            'family' => ['primary', 'secondary', 'adult', 'teenager', 'child', 'infant'],
            'government' => ['executive', 'director', 'manager', 'supervisor', 'officer', 'assistant'],
            'educational_institution' => ['chancellor', 'dean', 'professor', 'associate', 'assistant', 'lecturer'],
            'organisation' => ['executive', 'manager', 'lead', 'senior', 'mid', 'entry'],
            'social_group' => ['leader', 'coordinator', 'member', 'volunteer'],
            'professional_group' => ['senior', 'mid', 'junior', 'apprentice'],
            'non_profit' => ['executive', 'manager', 'coordinator', 'volunteer'],
            'individual' => ['primary', 'secondary', 'tertiary'],
        ];

        return $levelMappings[$userType] ?? $levelMappings['organisation'];
    }
} 