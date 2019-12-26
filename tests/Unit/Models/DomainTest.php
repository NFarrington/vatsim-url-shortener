<?php

namespace Tests\Unit\Models;

use App\Models\Domain;
use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Models\Domain
 */
class DomainTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function has_many_urls()
    {
        /* @var Domain $domain */
        $domain = create(Domain::class);
        $url1 = create(Url::class, ['domain_id' => $domain->id]);
        $url2 = create(Url::class, ['domain_id' => $domain->id]);

        $actualUrlIds = $domain->urls->pluck('id');

        $this->assertContains($url1->id, $actualUrlIds);
        $this->assertContains($url2->id, $actualUrlIds);
    }

    /** @test */
    function set_url_attribute_adds_missing_slashes()
    {
        $domain = create(Domain::class, ['url' => 'https://example.com']);
        $this->assertEquals('https://example.com/', $domain->url);
    }

    /** @test */
    function set_url_attribute_does_not_add_unnecessary_slashes()
    {
        $domain = create(Domain::class, ['url' => 'https://example.com/']);
        $this->assertEquals('https://example.com/', $domain->url);
    }
}
