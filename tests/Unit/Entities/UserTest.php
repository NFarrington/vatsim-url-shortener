<?php

namespace Tests\Unit\Entities;

use App\Entities\EmailVerification;
use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\Url;
use App\Entities\User;
use App\Services\VatsimService;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Tests\Traits\RefreshDatabase;
use stdClass;
use Tests\TestCase;

/**
 * @covers \App\Entities\User
 */
class UserTest extends TestCase
{
    use ArraySubsetAsserts, RefreshDatabase;

    /** @test */
    function provides_full_name()
    {
        $user = create(User::class, ['firstName' => 'Monty', 'lastName' => 'Burns']);

        $fullName = $user->getFullName();

        $this->assertEquals("Monty Burns", $fullName);
    }

    /** @test */
    function provides_display_info()
    {
        $user = create(User::class, ['id' => 7, 'firstName' => 'Homer', 'lastName' => 'Simpson']);

        $displayInfo = $user->getDisplayInfo();

        $this->assertEquals('Homer Simpson (7)', $displayInfo);
    }
}
