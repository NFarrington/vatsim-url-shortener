<?php

namespace App\Http\Middleware;

use Closure;

class TwoFactorAuth
{
    /**
     * The URIs that should be excluded from 2FA verification.
     *
     * @var array
     */
    protected $except = [
        'platform/login/two-factor',
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->inExceptArray($request) || ($request->user() && !$request->user()->totp_secret)) {
            return $next($request);
        }

        if ($request->session()->has('auth.two-factor')) {
            return $next($request);
        }

        return redirect()->guest(route('platform.login.two-factor'));
    }

    /**
     * Determine if the request has a URI that should pass through 2FA verification.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     *
     * @internal Illuminate\Foundation\Http\Middleware\VerifyCsrfToken
     */
    protected function inExceptArray($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
