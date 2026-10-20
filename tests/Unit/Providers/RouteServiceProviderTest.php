<?php

namespace Tests\Unit\Providers;

use App\Providers\RouteServiceProvider;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\RateLimiter as RateLimiterFacade;
use Tests\TestCase;

class RouteServiceProviderTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function forces_the_url_scheme()
    {
        $this->app['url']->forceScheme('http');
        config()->set('app.force_scheme', 'https');
        $provider = new RouteServiceProvider($this->app);

        $provider->boot();

        $this->assertEquals('https://', $this->app['url']->formatScheme());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registers_rate_limiters_when_routes_are_cached()
    {
        // With cached routes, map() isn't called, so only boot() runs.
        RateLimiterFacade::swap(new RateLimiter($this->app['cache']->driver()));
        $provider = new RouteServiceProvider($this->app);

        $provider->boot();

        $this->assertNotNull(RateLimiterFacade::limiter('api'));
        $this->assertNotNull(RateLimiterFacade::limiter('two-factor'));
    }
}
