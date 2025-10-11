<?php

namespace App\Http\Controllers;

use App\Helpers\AuditLogger;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Notifications\SendOtpNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    private function checkUserAccess()
    {
        $user = Auth::user();
        if ($user && $user->type === 'individual') {
            abort(404, 'User management not available for individual accounts');
        }
        if (!$user->is_admin) {
            abort(403, 'Access denied. Only administrators can manage users.');
        }
    }

    public function index(Request $request)
    {
        $this->checkUserAccess();
        $currentUser = Auth::user();
        
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            return $this->getUsersDataTable($request, $currentUser);
        }

        return view('users.index');
    }

    private function getUsersDataTable(Request $request, $currentUser)
    {
        $draw = $request->get('draw');
        $start = $request->get('start');
        $length = $request->get('length');
        $search = $request->get('search')['value'] ?? '';
        $orderColumn = $request->get('order')[0]['column'] ?? 0;
        $orderDir = $request->get('order')[0]['dir'] ?? 'asc';

        // Define columns for ordering
        $columns = ['name', 'email', 'phone', 'positions.name', 'email_verified_at'];
        $orderBy = $columns[$orderColumn] ?? 'name';

        // Base query with proper organizational isolation
        $query = User::with(['positions.department'])
            ->where('type', $currentUser->type)
            ->where('type_name', $currentUser->type_name)
            ->where('id', '!=', $currentUser->id);

        // Apply search if provided
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('positions', function($posQuery) use ($search) {
                      $posQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Get total count before pagination
        $totalRecords = $query->count();

        // Apply ordering
        if (strpos($orderBy, '.') !== false) {
            // Handle related model ordering (positions.name)
            if ($orderBy === 'positions.name') {
                $query->leftJoin('user_positions', function($join) {
                    $join->on('users.id', '=', 'user_positions.user_id')
                         ->where('user_positions.is_primary', true)
                         ->where('user_positions.is_active', true);
                })
                ->leftJoin('positions', 'user_positions.position_id', '=', 'positions.id')
                ->orderBy('positions.name', $orderDir)
                ->select('users.*');
            }
        } else {
            $query->orderBy($orderBy, $orderDir);
        }

        // Apply pagination
        $users = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $data = [];
        foreach ($users as $user) {
            $primaryPosition = $user->positions->where('pivot.is_primary', true)->first();
            
            $data[] = [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'is_admin' => $user->is_admin,
                'email_verified_at' => $user->email_verified_at,
                'position' => $primaryPosition ? [
                    'id' => $primaryPosition->id,
                    'name' => $primaryPosition->name,
                    'level' => $primaryPosition->level,
                    'department_name' => $primaryPosition->department->name
                ] : null,
                'avatar_color' => '#' . substr(md5($user->email), 0, 6),
                'initials' => strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1))
            ];
        }

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => User::where('type', $currentUser->type)->where('type_name', $currentUser->type_name)->where('id', '!=', $currentUser->id)->count(),
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }

    public function create()
    {
        $this->checkUserAccess();
        $currentUser = Auth::user();
        
        // Get departments and positions for the current user's organization
        $departments = Department::with('activePositions')
            ->forUserType($currentUser->type)
            ->where('type', $currentUser->type_name)
            ->active()
            ->get();

        return view('users.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $this->checkUserAccess();
        $currentUser = Auth::user();
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'position_id' => 'nullable|exists:positions,id',
            'is_admin' => 'boolean',
            'start_date' => 'nullable|date',
        ]);

        // Generate a random password
        $password = $this->generatePassword();
        
        // Create the user (they will need to verify via OTP on first login)
        $user = User::create([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($password),
            'type' => $currentUser->type,
            'type_name' => $currentUser->type_name,
            'is_admin' => $request->boolean('is_admin'),
            // Don't auto-verify - users must complete OTP verification on first login
        ]);

        // Debug logging
        Log::info('Created user: ' . $user->email . ', email_verified_at: ' . ($user->email_verified_at ? $user->email_verified_at->toDateTimeString() : 'NULL'));

        // Assign position if provided
        if ($request->position_id) {
            $position = Position::findOrFail($request->position_id);
            $user->positions()->attach($position->id, [
                'start_date' => $request->start_date ?? now(),
                'is_primary' => true, // First position is primary
                'is_active' => true,
            ]);
            AuditLogger::log("User {$user->email} created and assigned to position {$position->name}");
        } else {
            AuditLogger::log("User {$user->email} created without position assignment");
        }

        // Send credentials via email and SMS
        $this->sendCredentials($user, $password);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully and credentials have been sent!');
    }

    public function edit(User $user)
    {
        $this->checkUserAccess();
        $currentUser = Auth::user();
        
        // Ensure user belongs to same organization/type
        if ($user->type !== $currentUser->type || $user->type_name !== $currentUser->type_name) {
            abort(403);
        }

        $departments = Department::with('activePositions')
            ->forUserType($currentUser->type)
            ->where('type', $currentUser->type_name)
            ->active()
            ->get();

        $user->load(['positions.department']);

        return view('users.edit', compact('user', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $this->checkUserAccess();
        $currentUser = Auth::user();
        
        // Ensure user belongs to same organization/type
        if ($user->type !== $currentUser->type || $user->type_name !== $currentUser->type_name) {
            abort(403);
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20'],
            'position_id' => 'nullable|exists:positions,id',
            'is_admin' => 'boolean',
            'start_date' => 'nullable|date',
        ]);

        $user->update([
            'name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_admin' => $request->boolean('is_admin'),
        ]);

        // Update position assignment if provided
        if ($request->position_id) {
            $position = Position::findOrFail($request->position_id);
            
            // Remove current positions and assign new one
            $user->positions()->updateExistingPivot($user->positions->first()->id ?? 0, ['is_active' => false]);
            
            $user->positions()->syncWithoutDetaching([
                $position->id => [
                    'start_date' => $request->start_date ?? now(),
                    'is_primary' => true,
                    'is_active' => true,
                ]
            ]);
            
            AuditLogger::log("User {$user->email} updated and assigned to position {$position->name}");
        } else {
            // Remove all positions if no position is selected
            $user->positions()->updateExistingPivot($user->positions->pluck('id'), ['is_active' => false]);
            AuditLogger::log("User {$user->email} updated with no position assignment");
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        $this->checkUserAccess();
        $currentUser = Auth::user();
        
        // Ensure user belongs to same organization/type and not deleting self
        if ($user->type !== $currentUser->type || $user->id === $currentUser->id) {
            abort(403);
        }

        AuditLogger::log("User {$user->email} deleted");

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function toggleAdmin(User $user)
    {
        $this->checkUserAccess();
        $currentUser = Auth::user();
        
        // Ensure user belongs to same organization/type and current user is admin
        if ($user->type !== $currentUser->type || !$currentUser->is_admin) {
            abort(403);
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $status = $user->is_admin ? 'granted' : 'revoked';
        AuditLogger::log("Admin privileges {$status} for user {$user->email}");

        return response()->json([
            'success' => true,
            'is_admin' => $user->is_admin,
            'message' => "Admin privileges {$status} successfully!"
        ]);
    }

    public function resendCredentials(User $user)
    {
        $this->checkUserAccess();
        $currentUser = Auth::user();
        
        // Ensure user belongs to same organization/type
        if ($user->type !== $currentUser->type) {
            abort(403);
        }

        // Generate new password
        $password = $this->generatePassword();
        
        // Update user password
        $user->update(['password' => Hash::make($password)]);

        // Send new credentials
        $this->sendCredentials($user, $password);

        AuditLogger::log("Credentials resent to user {$user->email}");

        return response()->json([
            'success' => true,
            'message' => 'New credentials have been sent successfully!'
        ]);
    }

    private function generatePassword($length = 12)
    {
        return Str::random($length);
    }

    private function sendCredentials(User $user, string $password)
    {
        // Send email with credentials
        try {
            $user->notify(new \App\Notifications\UserCredentialsNotification($password));
        } catch (\Exception $e) {
            Log::error('Failed to send email credentials: ' . $e->getMessage());
        }

        // Send SMS with credentials
        $message = "Welcome to GoalDocs! Your login credentials:\nEmail: {$user->email}\nPassword: {$password}\nIMPORTANT: You'll need to verify via OTP on first login. Please change your password after completing verification.";
        
        try {
            $query = http_build_query([
                'username'   => config('services.deywuro_sms.username'),
                'password'   => config('services.deywuro_sms.password'),
                'source'     => config('services.deywuro_sms.source'),
                'destination'=> $user->phone,
                'message'    => $message,
            ]);

            $url = config('services.deywuro_sms.url') . '?' . $query;
            $response = Http::get($url);
            
            Log::info('SMS credentials sent to ' . $user->phone . ' with response: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Failed to send SMS credentials: ' . $e->getMessage());
        }
    }
} 