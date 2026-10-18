<?php

namespace Tests\Feature\Platform;

use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function login_page_loads_successfully()
    {
        $this->get(route('platform.login'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function authenticated_user_is_redirected_from_login_page()
    {
        $this->signIn();
        $this->get(route('platform.login'))
            ->assertRedirect();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_logout()
    {
        $this->signIn();
        Session::put('test', true);

        $this->post(route('platform.logout'))
            ->assertRedirect()
            ->assertSessionMissing('test');
    }
}
