<?php

namespace App\Listeners;

use App\Events\UrlEvent;
use App\Services\UrlService;

class CacheShortUrl
{
    protected UrlService $urlService;

    public function __construct(UrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    public function handle(UrlEvent $event)
    {
        $url = $event->url;

        if (!$url) {
            // url no longer exists
            return;
        }

        $this->urlService->addUrlToCache($url);
    }
}
