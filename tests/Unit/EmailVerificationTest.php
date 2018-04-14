<?php

namespace Tests\Unit;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function email_verification_has_user()
    {
        $user = create(User::class);
        $verification = create(EmailVerification::class, ['user_id' => $user->id]);
        $this->assertEquals($user->id, $verification->user->id);
    }
}
