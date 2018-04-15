<?php

namespace Tests\Feature\Platform;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function test_admin_page_redirects()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin'))
            ->assertRedirect();
    }

    /** @test */
    function test_admin_pages_are_inaccessible_to_non_admins()
    {
        $this->expectException(AuthorizationException::class);

        $this->signIn();

        $this->get(route('platform.admin'))
            ->assertForbidden();
    }
}
