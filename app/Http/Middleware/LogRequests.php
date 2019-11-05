<?php

namespace App\Http\Middleware;

use App\Models\Url;
use App\Models\UrlAnalytics;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    /**
     * Headers that should not be logged.
     *
     * @var array
     */
    protected $guardedHeaders = [
        'http_cookie',
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
        return $next($request);
    }

    /**
     * Log information about the request and response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function terminate(Request $request, Response $response)
    {
        /** @var Url $url */
        $url = $request->session()->pull('short.url');
        $analyticsDisabled = $url && $url->analytics_disabled;

        if (!$analyticsDisabled) {
            $serverData = $this->filterFillable($_SERVER);
            UrlAnalytics::create([
                'user_id' => Auth::check() ? Auth::user()->id : null,
                'url_id' => $url ? $url->id : null,
                'request_time' => $_SERVER['REQUEST_TIME'],
                'http_host' => $request->root(),
                'http_referer' => $request->headers->get('referer'),
                'http_user_agent' => $request->userAgent(),
                'remote_addr' => $request->ip(),
                'request_uri' => $request->path(),
                'get_data' => $_GET,
                'post_data' => $_POST,
                'custom_headers' => array_diff_key($this->getHeaders(), $serverData),
                'response_code' => $response->getStatusCode(),
            ]);
        }
    }

    /**
     * Filters the provided array so that only fillable attributes are returned.
     *
     * @param $arr
     * @return array
     */
    protected function filterFillable($arr)
    {
        return array_intersect_key(array_change_key_case($arr), array_flip((new UrlAnalytics)->getFillable()));
    }

    /**
     * Get the request headers that aren't guarded by $this->guardedHeaders.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return array_filter_key(array_change_key_case($_SERVER), function ($key) {
            return Str::startsWith($key, 'http_') && !array_key_exists($key, array_flip($this->guardedHeaders));
        });
    }
}
