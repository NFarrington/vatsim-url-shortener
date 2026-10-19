<?php

namespace Tests\Unit\Entities;

use App\Entities\EmailVerification;
use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\Url;
use App\Entities\User;
use App\Services\VatsimService;
use Tests\Traits\RefreshDatabase;
use stdClass;
use Tests\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\App\Entities\User::class)]
class UserTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function provides_full_name()
    {
        $user = create(User::class, ['firstName' => 'Monty', 'lastName' => 'Burns']);

        $fullName = $user->getFullName();

        $this->assertEquals("Monty Burns", $fullName);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function provides_display_info()
    {
        $user = create(User::class, ['id' => 7, 'firstName' => 'Homer', 'lastName' => 'Simpson']);

        $displayInfo = $user->getDisplayInfo();

        $this->assertEquals('Homer Simpson (7)', $displayInfo);
    }
}
