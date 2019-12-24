<?php

namespace Tests\Unit\Models;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function belongs_to_user()
    {
        $user = create(User::class);
        $verification = create(EmailVerification::class, ['user_id' => $user->id]);

        $actualUser = $verification->user;

        $this->assertEquals($user->id, $actualUser->id);
    }
}
