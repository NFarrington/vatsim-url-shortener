<?php

namespace Tests\Unit;

use App\Models\EmailVerification;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function user_has_email_verification()
    {
        $user = create(User::class);
        $verification = create(EmailVerification::class, ['user_id' => $user->id]);
        $this->assertEquals($verification->id, $user->emailVerification->id);
    }

    /** @test */
    function user_has_urls()
    {
        $user = create(User::class);
        $url = create(Url::class, ['user_id' => $user->id]);
        $this->assertEquals($url->id, $user->urls->first()->id);
    }

    /** @test */
    function user_has_full_name()
    {
        $user = create(User::class);
        $this->assertEquals("{$user->first_name} {$user->last_name}", $user->full_name);
    }

    /** @test */
    function user_is_admin()
    {
        $user = create(User::class);
        config(['auth.admins' => [$user->id]]);
        $this->assertTrue($user->isAdmin());
    }

    /** @test */
    function user_is_not_admin()
    {
        $user = create(User::class);
        config(['auth.admins' => []]);
        $this->assertFalse($user->isAdmin());
    }
}
