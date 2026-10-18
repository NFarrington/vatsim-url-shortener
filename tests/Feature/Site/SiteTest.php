<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class SiteTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_loads_successfully()
    {
        $this->get(route('site.home'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function about_page_loads_successfully()
    {
        $this->get(route('site.about'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function contact_page_loads_successfully()
    {
        $this->get(route('site.contact'))
            ->assertStatus(200);
    }
}
