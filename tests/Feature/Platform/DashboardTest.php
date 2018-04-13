<?php

namespace Tests\Feature\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function dashboard_page_loads_successfully()
    {
        $this->signIn();

        $this->get(route('platform.dashboard'))
            ->assertStatus(200);
    }
}
