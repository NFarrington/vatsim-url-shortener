<?php

namespace Tests\Feature\Platform\Admin;

use Illuminate\Auth\Access\AuthorizationException;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function test_admin_page_redirects()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin'))
            ->assertRedirect();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function test_admin_pages_are_inaccessible_to_non_admins()
    {
        $this->withExceptionHandling();

        $this->signIn();

        $this->get(route('platform.admin'))
            ->assertForbidden();
    }
}
