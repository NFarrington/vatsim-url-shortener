<?php

namespace App\Http\Controllers;

use App\Events\UrlRetrieved;
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

        $url = $this->urlService->getRedirectForUrl($request->root().'/', $shortUrl, $prefix);

        // the 'retrieved' event on the model causes infinite recursion when
        // being unserialised on the queue, so we fire it manually here
        event(new UrlRetrieved($url));

        $response = redirect()->to($url->redirect_url);
        $response->shortUrl = $url;

        return $response;
    }
}
