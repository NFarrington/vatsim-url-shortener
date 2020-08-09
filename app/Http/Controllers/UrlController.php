<?php

namespace App\Http\Controllers;

use App\Services\UrlService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UrlController extends Controller
{
    private UrlService $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->middleware('log-requests');
        $this->urlService = $urlService;
    }

    /**
     * Redirect a short URL to the intended URL.
     *
     * @param Request $request
     * @param string|null $prefix
     * @param string|null $shortUrl
     * @return Response
     */
    public function redirect(Request $request, string $prefix = null, string $shortUrl = null): Response
    {
        if ($prefix && !$shortUrl) {
            $shortUrl = $prefix;
            $prefix = null;
        }

        $ipAddressRegex = '(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)';
        if (preg_match(sprintf('/^%s$/', $ipAddressRegex), $request->getHost()) === 1) {
            $url = $this->urlService->getRedirectForUrl(config('app.url').'/', $shortUrl, $prefix);
        } else {
            $url = $this->urlService->getRedirectForUrl($request->root().'/', $shortUrl, $prefix);
        }

        $response = redirect()->to($url->getRedirectUrl());

        $response->shortUrl = $url; // used by App\Http\Middleware\LogRequests

        return $response;
    }
}
