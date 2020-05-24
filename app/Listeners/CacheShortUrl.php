<?php

namespace App\Listeners;

use App\Events\UrlRetrieved;
use App\Events\UrlSaved;
use App\Models\Url;
use Cache;
use Illuminate\Contracts\Queue\ShouldQueue;

class CacheShortUrl implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param UrlSaved|UrlRetrieved $event
     * @return void
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException
     */
    public function handle($event)
    {
        /** @var Url $url */
        $url = $event->url;

        // If the model is retrieved with a subset of fields (e.g. using pluck()),
        // there isn't enough information to be able to cache the result. As
        // the caching operation is unimportant, we can simply do nothing.
        $modelAttributes = $url->attributesToArray();
        if (!array_key_exists('domain_id', $modelAttributes)
            || !array_key_exists('organization_id', $modelAttributes)
            || !array_key_exists('url', $modelAttributes)
            || !array_key_exists('prefix', $modelAttributes)) {
            return;
        }

        $domain = $url->domain->url;
        $prefix = $url->prefix ? $url->organization->prefix : null;
        $urlName = $url->url;

        Cache::set(sprintf(Url::URL_CACHE_KEY, $domain, $prefix, $urlName), $url);
    }
}
