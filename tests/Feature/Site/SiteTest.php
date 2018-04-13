<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class SiteTest extends TestCase
{
    /** @test */
    public function index_page_loads_successfully()
    {
        $this->get(route('site.home'))
            ->assertStatus(200);
    }

    /** @test */
    public function about_page_loads_successfully()
    {
        $this->get(route('site.about'))
            ->assertStatus(200);
    }

    /** @test */
    public function contact_page_loads_successfully()
    {
        $this->get(route('site.contact'))
            ->assertStatus(200);
    }
}
