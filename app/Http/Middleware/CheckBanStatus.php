<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckBanStatus
{
    /**
     * The URIs that should be excluded from ban validation.
     *
     * @var array
     */
    protected $except = [
        'platform/register',
        'platform/login',
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($this->inExceptArray($request)) {
            return $next($request);
        }

        $user = $request->user();
        if ($user && ($user->isBanned() || in_array($user->getId(), config('auth.banned_users')))) {
            Log::info('Request cancelled: banned user.', ['user' => $user]);

            auth()->guard()->logout();
            $request->session()->invalidate();

            return redirect()->route('platform.login')
                ->with('error', 'Request failed. You are not authorized to use this service.');
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param \Illuminate\Http\Request $request
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
