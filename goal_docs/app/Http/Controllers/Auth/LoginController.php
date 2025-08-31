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
        Log::info('Login attempt for user: ' . $user->email . ', email_verified_at: ' . ($user->email_verified_at ? $user->email_verified_at->toDateTimeString() : 'NULL'));

        // Redirect to dashboard - middleware will handle verification check
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        // Clear MFA verification session
        $request->session()->forget('mfa_verified');
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Add cache-busting parameter to prevent cached pages
        return redirect('/?logout=' . time());
    }
}