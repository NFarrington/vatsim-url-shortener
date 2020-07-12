<?php

namespace App\Listeners;

use App\Entities\Url;
use App\Events\UrlEvent;
use Cache;

class CacheShortUrl
{
    public function handle(UrlEvent $event)
    {
        $url = $event->url;

        if (!$url) {
            // url no longer exists
            return;
        }

        $domain = $url->getDomain()->getUrl();
        $prefix = $url->getPrefix() ? $url->getOrganization()->getPrefix() : null;
        $urlName = $url->getUrl();

        Cache::set(sprintf(Url::URL_CACHE_KEY, $domain, $prefix, $urlName), $url);
    }
}
