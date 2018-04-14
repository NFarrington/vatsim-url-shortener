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
    public function user_has_email_verification()
    {
        $user = create(User::class);
        $verification = create(EmailVerification::class, ['user_id' => $user->id]);
        $this->assertEquals($verification->id, $user->emailVerification->id);
    }

    /** @test */
    public function user_has_urls()
    {
        $user = create(User::class);
        $url = create(Url::class, ['user_id' => $user->id]);
        $this->assertEquals($url->id, $user->urls->first()->id);
    }

    /** @test */
    public function user_has_full_name()
    {
        $user = create(User::class);
        $this->assertEquals("{$user->first_name} {$user->last_name}", $user->full_name);
    }
}
