<?php

namespace Tests\Unit;

use App\Models\Domain;
use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function domains_have_urls()
    {
        $domain = create(Domain::class);
        $url = create(Url::class, ['domain_id' => $domain->id]);

        $this->assertEquals($url->id, $domain->urls->first()->id);
    }

    /** @test */
    function domains_always_end_with_slashes()
    {
        $domain = create(Domain::class, ['url' => 'https://example.com']);
        $this->assertEquals('https://example.com/', $domain->url);
    }
}
