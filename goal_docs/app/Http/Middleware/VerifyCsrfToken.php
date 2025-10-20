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
        // Remove login from exceptions to enable CSRF protection
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
            // Gracefully recover from token mismatches: reset session + token and redirect back
            try {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } catch (\Throwable $t) {
                // ignore
            }

            // Prefer redirecting back to the previous page if possible
            $fallback = route('login');
            $target = url()->previous() ?: $fallback;

            return redirect($target)
                ->withErrors(['csrf' => 'Your session expired. Please try that action again.'])
                ->withInput($request->except(['password', '_token']));
        }
    }
}
