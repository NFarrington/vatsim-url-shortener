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
}
