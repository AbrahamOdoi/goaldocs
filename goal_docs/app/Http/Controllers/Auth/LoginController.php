<?php
// app/Http/Controllers/Auth/LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $user->updateLastLogin();

        // Debug logging
        Log::info('Login successful for user: ' . $user->email . ', email_verified_at: ' . ($user->email_verified_at ? $user->email_verified_at->toDateTimeString() : 'NULL'));

        // Always clear previous MFA flag on fresh login
        $request->session()->forget('mfa_verified');

        // Redirect deterministically based on verification state to avoid loops
        if (is_null($user->email_verified_at)) {
            return redirect()->route('verification.method');
        }
        return redirect()->route('verification.method'); // enforce MFA every login
    }

    // public function logout(Request $request)
    // {
    //     // Clear MFA verification session
    //     $request->session()->forget('mfa_verified');
        
    //     Auth::logout();
        
    //     // Only invalidate session, don't regenerate token
    //     $request->session()->invalidate();
        
    //     // Force a fresh page load by redirecting to login with a timestamp
    //     return redirect('/login?logout=' . time())->with('success', 'You have been logged out successfully.');
    // }

    public function logout(Request $request)
    {
        // Clear MFA verification session
        $request->session()->forget('mfa_verified');
        
        Auth::logout();
        
        // CRITICAL: Must invalidate AND regenerate token
        $request->session()->invalidate();
        $request->session()->regenerateToken();  // ← ADD THIS LINE
        
        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }
}