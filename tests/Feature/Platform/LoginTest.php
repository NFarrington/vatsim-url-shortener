<?php

namespace Tests\Feature\Platform;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function login_page_loads_successfully()
    {
        $this->get(route('login'))
            ->assertStatus(200);
    }

    /** @test */
    function authenticated_user_is_redirected_from_login_page()
    {
        $this->signIn();
        $this->get(route('login'))
            ->assertRedirect();
    }

    /** @test */
    function user_can_logout()
    {
        $this->signIn();
        Session::put('test', true);

        $this->post(route('logout'))
            ->assertRedirect()
            ->assertSessionMissing('test');
    }
}
