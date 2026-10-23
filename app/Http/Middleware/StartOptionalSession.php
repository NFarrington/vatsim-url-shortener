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
     * Resume the visitor's session if the request carries a session cookie.
     *
     * Requests without one (anonymous visitors to short URLs) are handled
     * without a session, so no session is stored and no cookie is set.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->cookies->has($this->manager->getSessionConfig()['cookie'])) {
            return $next($request);
        }

        try {
            return parent::handle($request, $next);
        } catch (Exception $e) {
            Log::debug('Failed to start optional session.', ['exception' => $e]);

            return $next($request);
        }
    }
}
