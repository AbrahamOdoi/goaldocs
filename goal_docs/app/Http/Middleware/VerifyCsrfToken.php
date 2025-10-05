<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'login',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            // If it's a login request and we get a token mismatch, redirect to login with error
            if ($request->is('login') && $request->isMethod('post')) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Your session has expired. Please try logging in again.'])
                    ->withInput($request->except('password', '_token'));
            }
            
            // For other requests, throw the exception as normal
            throw $e;
        }
    }
}
