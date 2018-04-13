<?php

namespace Tests\Feature\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_page_loads_successfully()
    {
        $this->get(route('login'))
            ->assertStatus(200);
    }

    /** @test */
    public function authenticated_user_is_redirected_from_login_page()
    {
        $this->signIn();
        $this->get(route('login'))
            ->assertRedirect();
    }
}
