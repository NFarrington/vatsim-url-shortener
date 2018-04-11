<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeTest extends TestCase
{
    /** @test */
    public function index_page_loads_successfully()
    {
        $this->get(route('home'))
            ->assertStatus(200);
    }

    /** @test */
    public function about_page_loads_successfully()
    {
        $this->get(route('about'))
            ->assertStatus(200);
    }

    /** @test */
    public function contact_page_loads_successfully()
    {
        $this->get(route('contact'))
            ->assertStatus(200);
    }

    /** @test */
    public function dashboard_page_loads_successfully()
    {
        $this->get(route('dashboard'))
            ->assertStatus(200);
    }
}
