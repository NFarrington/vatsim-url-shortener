<?php

namespace Tests\Unit\Entities;

use App\Entities\EmailVerification;
use App\Entities\User;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\App\Entities\EmailVerification::class)]
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
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
