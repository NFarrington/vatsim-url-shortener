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
    function caches_a_url()
    {
        $url = null;
        Event::fakeFor(function () use (&$url) {
            $url = factory(Url::class)->states('org')->create();
        });

        event(new UrlSaved($url));

        $this->assertTrue(cache()->has(sprintf(Url::URL_CACHE_KEY, $url->domain->url, $url->organization->prefix, $url->url)));
    }
}
