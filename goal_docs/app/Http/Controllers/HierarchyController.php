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
            'type' => $this->getTypeForUserType($userType),
            'user_type' => $userType,
            'color' => $request->color,
        ]);

        return redirect()->route('hierarchy.index')->with('success', ucfirst($this->getDisplayName($userType)) . ' created successfully!');
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

        return redirect()->route('hierarchy.index')->with('success', ucfirst($this->getDisplayName($department->user_type)) . ' updated successfully!');
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
        ]);

        return redirect()->route('hierarchy.index')->with('success', 'Position created successfully!');
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

        return redirect()->route('hierarchy.index')->with('success', 'Position updated successfully!');
    }

    public function assignPosition(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'position_id' => 'required|exists:positions,id',
            'is_primary' => 'boolean',
            'start_date' => 'nullable|date',
        ]);

        $user = User::findOrFail($request->user_id);
        $position = Position::findOrFail($request->position_id);

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

        $user = User::findOrFail($request->user_id);
        $user->positions()->detach($request->position_id);

        return response()->json(['success' => true, 'message' => 'Position removed successfully!']);
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