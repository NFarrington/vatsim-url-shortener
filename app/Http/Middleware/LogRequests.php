<?php

namespace App\Http\Middleware;

use App\Entities\Url;
use App\Entities\UrlAnalytics;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
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

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
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
        /** @var Url|null $url */
        $url = null;
        if (property_exists($response, 'shortUrl')) {
            $url = $response->shortUrl;
        }

        $analyticsDisabled = $url && $url->isAnalyticsDisabled();

        if (!$analyticsDisabled) {
            $urlAnalytic = new UrlAnalytics();
            $urlAnalytic->setUser(Auth::check() ? Auth::user() : null);
            $urlAnalytic->setUrl($url ? $url : null);
            $urlAnalytic->setRequestTime($_SERVER['REQUEST_TIME']);
            $urlAnalytic->setHttpHost($request->root());
            $urlAnalytic->setHttpReferer($request->headers->get('referer'));
            $urlAnalytic->setHttpUserAgent($request->userAgent());
            $urlAnalytic->setRemoteAddr($request->ip());
            $urlAnalytic->setRequestUri($request->path());
            $urlAnalytic->setGetData($_GET);
            //$urlAnalytic->setPostData($_POST);
            // headers that aren't added to other fields
            $urlAnalytic->setCustomHeaders(array_diff_key($this->getHeaders(), $this->filterFillable($_SERVER)));
            $urlAnalytic->setResponseCode($response->getStatusCode());
            $this->em->persist($urlAnalytic);
            $this->em->flush();
        }
    }

    /**
     * Filters the provided array so that only fillable attributes are returned.
     *
     * @param $arr
     * @return array
     */
    protected function filterFillable(array $arr)
    {
        $fillable = [
            'http_host',
            'http_referer',
            'http_user_agent',
        ];

        return array_intersect_key(array_change_key_case($arr), array_flip($fillable));
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
