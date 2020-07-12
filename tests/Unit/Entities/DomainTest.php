<?php

namespace Tests\Unit\Entities;

use App\Entities\Domain;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Entities\Domain
 */
class DomainTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function set_url_attribute_adds_missing_slashes()
    {
        $domain = make(Domain::class);

        $domain->setUrl('https://example.com');

        $this->assertEquals('https://example.com/', $domain->getUrl());
    }

    /** @test */
    function set_url_attribute_does_not_add_unnecessary_slashes()
    {
        $domain = make(Domain::class);

        $domain->setUrl('https://example.com/');

        $this->assertEquals('https://example.com/', $domain->getUrl());
    }
}
