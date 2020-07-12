<?php

namespace Tests\Unit\Listeners;

use App\Events\UrlSaved;
use App\Entities\Url;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CacheShortUrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function caches_prefixed_urls_with_a_prefix()
    {
        $url = null; /** @var Url $url */
        Event::fakeFor(function () use (&$url) {
            $url = entity(Url::class)->states('org')->create(['prefix' => true]);
        });

        event(new UrlSaved($url));

        $this->assertTrue(cache()->has(sprintf(Url::URL_CACHE_KEY, $url->getDomain()->getUrl(), $url->getOrganization()->getPrefix(), $url->getUrl())));
        $this->assertFalse(cache()->has(sprintf(Url::URL_CACHE_KEY, $url->getDomain()->getUrl(), null, $url->getUrl())));
    }

    /** @test */
    function caches_non_prefixed_urls_without_a_prefix()
    {
        $url = null; /** @var Url $url */
        Event::fakeFor(function () use (&$url) {
            $url = entity(Url::class)->states('org')->create(['prefix' => false]);
        });

        event(new UrlSaved($url));

        $this->assertTrue(cache()->has(sprintf(Url::URL_CACHE_KEY, $url->getDomain()->getUrl(), null, $url->getUrl())));
        $this->assertFalse(cache()->has(sprintf(Url::URL_CACHE_KEY, $url->getDomain()->getUrl(), $url->getOrganization()->getPrefix(), $url->getUrl())));
    }
}
