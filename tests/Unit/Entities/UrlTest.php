<?php

namespace Tests\Unit\Entities;

use App\Entities\Domain;
use App\Entities\Organization;
use App\Entities\Url;
use App\Entities\User;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Entities\Url
 */
class UrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function provides_full_url()
    {
        $domain = make(Domain::class, ['url' => 'http://my-domain.test/']);
        $url = make(Url::class, ['domain' => $domain, 'url' => 'my-url']);

        $fullUrl = $url->getFullUrl();

        $this->assertEquals('http://my-domain.test/my-url', $fullUrl);
    }

    /** @test */
    function provides_full_url_with_prefix()
    {
        $domain = make(Domain::class, ['url' => 'http://my-domain.test/']);
        $organization = make(Organization::class, ['prefix' => 'my-prefix']);
        $url = make(Url::class, [
            'domain' => $domain, 'organization' => $organization, 'url' => 'my-url', 'prefix' => true
        ]);

        $fullUrl = $url->getFullUrl();

        $this->assertEquals('http://my-domain.test/my-prefix/my-url', $fullUrl);
    }
}
