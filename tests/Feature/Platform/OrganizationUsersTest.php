<?php

namespace Tests\Feature\Platform;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    function user_can_be_added_to_organization()
    {
        $organization = create(Organization::class);
        $this->user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_MANAGER]);
        $user = create(User::class);

        $this->get(route('platform.organizations.edit', $organization));
        $this->post(route('platform.organizations.users.store', $organization), [
            'id' => $user->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($organization->users()->getTable(), [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ]);
    }

    /** @test */
    function user_can_be_removed_from_organization()
    {
        $organization = create(Organization::class);
        $this->user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_MANAGER]);
        $user = create(User::class);
        $user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_MEMBER]);

        $this->get(route('platform.organizations.edit', $organization));
        $this->delete(route('platform.organizations.users.destroy', [$organization, $user]))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing($organization->users()->getTable(), [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    function user_cannot_remove_themselves_from_an_organization()
    {
        $organization = create(Organization::class);
        $this->user->organizations()->attach($organization, ['role_id' => OrganizationUser::ROLE_MANAGER]);

        $this->get(route('platform.organizations.edit', $organization));
        $this->delete(route('platform.organizations.users.destroy', [$organization, $this->user]))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas($organization->users()->getTable(), [
            'organization_id' => $organization->id,
            'user_id' => $this->user->id,
        ]);
    }
}
