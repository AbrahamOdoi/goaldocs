<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequireMfa
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
            Log::info('MFA Middleware check for user: ' . $user->email . ', mfa_verified: ' . (session('mfa_verified') ? 'true' : 'false') . ', route: ' . $request->route()->getName());
            
            // Check if MFA has been verified for this session
            if (!session('mfa_verified')) {
                $allowedRoutes = [
                    'verification.method',
                    'verification.send',
                    'verification.enter',
                    'verification.check',
                    'otp.resend',
                    'logout',
                    'mfa.required',
                    'mfa.verify'
                ];
                
                if (!in_array($request->route()->getName(), $allowedRoutes)) {
                    Log::info('Redirecting user to MFA verification: ' . $user->email . ' from route: ' . $request->route()->getName());
                    return redirect()->route('verification.method')
                        ->with('warning', 'Multi-factor authentication required. Please verify your identity.');
                }
            } else {
                Log::info('MFA verified, allowing access to: ' . $request->route()->getName());
            }
        }

        return $next($request);
    }
}
