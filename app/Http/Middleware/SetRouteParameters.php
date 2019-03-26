<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\URL;

class SetRouteParameters
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        URL::defaults([
            'site_domain' => $request->route()->parameter('site_domain'),
        ]);

        $request->route()->forgetParameter('site_domain');

        return $next($request);
    }
}
