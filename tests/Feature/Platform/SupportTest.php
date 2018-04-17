<?php

namespace Tests\Feature\Site;

use Tests\TestCase;

class SupportTest extends TestCase
{
    /** @test */
    function support_page_loads_successfully()
    {
        $this->get(route('platform.support'))
            ->assertStatus(200);
    }

    /** @test */
    function terms_page_loads_successfully()
    {
        $this->get(route('platform.terms'))
            ->assertStatus(200);
    }

    /** @test */
    function privacy_page_loads_successfully()
    {
        $this->get(route('platform.privacy'))
            ->assertStatus(200);
    }
}
