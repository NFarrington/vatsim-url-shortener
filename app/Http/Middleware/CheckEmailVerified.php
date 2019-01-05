<?php

namespace App\Http\Middleware;

use Closure;

class CheckEmailVerified
{
    /**
     * The URIs that should be excluded from email verification.
     *
     * @var array
     */
    protected $except = [
        'platform/register',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->inExceptArray($request)) {
            $user = $request->user();

            if ($user->email && !$user->email_verified) {
                return redirect()->route('platform.register')
                    ->with('error', 'You must validate your email address before continuing.');
            }

            if (!$user->email) {
                return redirect()->route('platform.register');
            }
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
