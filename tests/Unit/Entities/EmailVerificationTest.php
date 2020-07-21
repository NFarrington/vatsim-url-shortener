<?php

namespace Tests\Unit\Entities;

use App\Entities\EmailVerification;
use App\Entities\User;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

/**
 * @covers \App\Entities\EmailVerification
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function belongs_to_user()
    {
        $user = create(User::class);
        $verification = create(EmailVerification::class, ['user' => $user]);
        EntityManager::clear();

        $actualUser = EntityManager::find(EmailVerification::class, $verification->getId())
            ->getUser();

        $this->assertEquals($user->getId(), $actualUser->getId());
    }
}
