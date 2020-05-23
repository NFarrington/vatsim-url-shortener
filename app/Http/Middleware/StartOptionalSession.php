<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession as Middleware;
use Illuminate\Support\Facades\Log;

class StartOptionalSession extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (Exception $e) {
            Log::debug('Failed to start optional session.', ['exception' => $e]);

            return $next($request);
        }
    }
}
