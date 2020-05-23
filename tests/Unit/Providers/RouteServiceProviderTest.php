<?php

namespace Tests\Unit\Providers;

use App\Providers\RouteServiceProvider;
use Tests\TestCase;

class RouteServiceProviderTest extends TestCase
{
    /** @test */
    public function forces_the_url_scheme()
    {
        $this->app['url']->forceScheme('http');
        config()->set('app.force_scheme', 'https');
        $provider = new RouteServiceProvider($this->app);

        $provider->boot();

        $this->assertEquals('https://', $this->app['url']->formatScheme());
    }
}
