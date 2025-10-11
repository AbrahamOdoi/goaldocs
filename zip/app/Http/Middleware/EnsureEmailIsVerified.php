<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Debug logging
            Log::info('Middleware check for user: ' . $user->email . ', email_verified_at: ' . ($user->email_verified_at ? $user->email_verified_at->toDateTimeString() : 'NULL') . ', route: ' . $request->route()->getName());
            
            // If user is not email verified, redirect to verification method selection
            // But allow access to verification routes themselves
            if (is_null($user->email_verified_at)) {
                $allowedRoutes = [
                    'verification.method',
                    'verification.send',
                    'verification.enter',
                    'verification.check',
                    'otp.resend',
                    'logout'
                ];
                
                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    Log::info('Redirecting unverified user to verification: ' . $user->email . ' from route: ' . $request->route()->getName());
                    return redirect()->route('verification.method')
                        ->with('warning', 'Please verify your account before accessing the application.');
                }
            } else {
                Log::info('User is verified, allowing access to: ' . $request->route()->getName());
            }
        }

        return $next($request);
    }
}
