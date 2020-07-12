<?php

namespace Tests\Unit\Entities\Traits;

use App\Entities\Entity;
use App\Entities\Traits\RecordsDataChanges;
use App\Entities\User;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\TestCase;
use Tests\Traits\RefreshDatabase;

/**
 * @covers \App\Entities\Traits\Revisionable
 */
class DataChangeTrackingTest extends TestCase
{
    use RefreshDatabase;

    private $revisionable;

    function setUp(): void
    {
        parent::setUp();
        $this->revisionable = new class extends Entity {
            use RecordsDataChanges;

            protected array $trackedProperties = ['my_property'];
        };
    }

    /** @test */
    function tracks_revisions()
    {
        $oldEmail = 'old@example.com';
        $newEmail = 'new@example.com';
        $user = create(User::class, ['email' => $oldEmail]);
        $user->setEmail($newEmail);
        EntityManager::flush();

        $this->assertDatabaseHas('revisions', [
            'model_id' => $user->getId(),
            'property_name' => 'email',
            'old_value' => $oldEmail,
            'new_value' => $newEmail,
        ]);
    }

    /** @test */
    function does_not_track_non_revisionable_properties()
    {
        $oldValue = 'Alpha';
        $newValue = 'Beta';
        $user = create(User::class, ['rememberToken' => $oldValue]);
        $user->setRememberToken($newValue);
        EntityManager::flush();

        $this->assertDatabaseMissing('revisions', [
            'model_id' => $user->getId(),
            'property_name' => 'rememberToken',
        ]);
    }
}
