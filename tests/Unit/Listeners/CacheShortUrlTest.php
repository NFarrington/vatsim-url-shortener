<?php

namespace Tests\Unit\Listeners;

use App\Events\UrlSaved;
use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CacheShortUrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function caches_prefixed_urls_with_a_prefix()
    {
        $url = null;
        Event::fakeFor(function () use (&$url) {
            $url = factory(Url::class)->states('org')->create(['prefix' => true]);
        });

        event(new UrlSaved($url));

        $this->assertTrue(cache()->has(sprintf(Url::URL_CACHE_KEY, $url->domain->url, $url->organization->prefix, $url->url)));
        $this->assertFalse(cache()->has(sprintf(Url::URL_CACHE_KEY, $url->domain->url, null, $url->url)));
    }

    /** @test */
    function caches_non_prefixed_urls_without_a_prefix()
    {
        $url = null;
        Event::fakeFor(function () use (&$url) {
            $url = factory(Url::class)->states('org')->create(['prefix' => false]);
        });

        event(new UrlSaved($url));

        $this->assertTrue(cache()->has(sprintf(Url::URL_CACHE_KEY, $url->domain->url, null, $url->url)));
        $this->assertFalse(cache()->has(sprintf(Url::URL_CACHE_KEY, $url->domain->url, $url->organization->prefix, $url->url)));
    }
}
