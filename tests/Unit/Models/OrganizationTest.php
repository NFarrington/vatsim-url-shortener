<?php

namespace Tests\Unit\Models;

use App\Models\Organization;
use App\Models\OrganizationPrefixApplication;
use App\Models\OrganizationUser;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function has_many_urls()
    {
        $organization = create(Organization::class);
        $expectedUrl1 = create(Url::class, ['organization_id' => $organization->id]);
        $expectedUrl2 = create(Url::class, ['organization_id' => $organization->id]);

        $actualUrlIds = $organization->urls->pluck('id');

        $this->assertEquals(2, $actualUrlIds->count());
        $this->assertContains($expectedUrl1->id, $actualUrlIds);
        $this->assertContains($expectedUrl2->id, $actualUrlIds);
    }

    /** @test */
    function belongs_to_many_owners()
    {
        $organization = create(Organization::class);
        $expectedOwner1 = create(User::class);
        $expectedOwner2 = create(User::class);
        $organization->owners()->newPivot([
            'organization_id' => $organization->id,
            'user_id' => $expectedOwner1->id,
            'role_id' => OrganizationUser::ROLE_OWNER,
        ])->save();
        $organization->owners()->newPivot([
            'organization_id' => $organization->id,
            'user_id' => $expectedOwner2->id,
            'role_id' => OrganizationUser::ROLE_OWNER,
        ])->save();

        $actualOwnerIds = $organization->owners->pluck('id');

        $this->assertEquals(2, $actualOwnerIds->count());
        $this->assertContains($expectedOwner1->id, $actualOwnerIds);
        $this->assertContains($expectedOwner2->id, $actualOwnerIds);
    }

    /** @test */
    function belongs_to_many_managers()
    {
        $organization = create(Organization::class);
        $expectedManager1 = create(User::class);
        $expectedManager2 = create(User::class);
        $organization->managers()->newPivot([
            'organization_id' => $organization->id,
            'user_id' => $expectedManager1->id,
            'role_id' => OrganizationUser::ROLE_MANAGER,
        ])->save();
        $organization->managers()->newPivot([
            'organization_id' => $organization->id,
            'user_id' => $expectedManager2->id,
            'role_id' => OrganizationUser::ROLE_MANAGER,
        ])->save();

        $actualManagerIds = $organization->managers->pluck('id');

        $this->assertEquals(2, $actualManagerIds->count());
        $this->assertContains($expectedManager1->id, $actualManagerIds);
        $this->assertContains($expectedManager2->id, $actualManagerIds);
    }

    /** @test */
    function belongs_to_many_members()
    {
        $organization = create(Organization::class);
        $expectedMember1 = create(User::class);
        $expectedMember2 = create(User::class);
        $organization->members()->newPivot([
            'organization_id' => $organization->id,
            'user_id' => $expectedMember1->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->save();
        $organization->members()->newPivot([
            'organization_id' => $organization->id,
            'user_id' => $expectedMember2->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->save();

        $actualMemberIds = $organization->members->pluck('id');

        $this->assertEquals(2, $actualMemberIds->count());
        $this->assertContains($expectedMember1->id, $actualMemberIds);
        $this->assertContains($expectedMember2->id, $actualMemberIds);
    }

    /** @test */
    function belongs_to_many_users()
    {
        $organization = create(Organization::class);
        $expectedUser1 = create(User::class);
        $expectedUser2 = create(User::class);
        $organization->users()->newPivot([
            'organization_id' => $organization->id,
            'user_id' => $expectedUser1->id,
            'role_id' => OrganizationUser::ROLE_OWNER,
        ])->save();
        $organization->users()->newPivot([
            'organization_id' => $organization->id,
            'user_id' => $expectedUser2->id,
            'role_id' => OrganizationUser::ROLE_MEMBER,
        ])->save();

        $actualUserIds = $organization->users->pluck('id');

        $this->assertEquals(2, $actualUserIds->count());
        $this->assertContains($expectedUser1->id, $actualUserIds);
        $this->assertContains($expectedUser2->id, $actualUserIds);
    }

    /** @test */
    function has_one_prefix_application()
    {
        $organization = create(Organization::class);
        $expectedApplication = create(OrganizationPrefixApplication::class, [
            'organization_id' => $organization->id,
        ]);

        $actualApplication = $organization->prefixApplication;

        $this->assertEquals($expectedApplication->id, $actualApplication->id);
    }

    /** @test */
    function does_not_have_deleted_prefix_applications()
    {
        $organization = create(Organization::class);
        create(OrganizationPrefixApplication::class, [
            'organization_id' => $organization->id,
            'deleted_at' => Carbon::now(),
        ]);

        $actualApplication = $organization->prefixApplication;

        $this->assertNull($actualApplication);
    }
}
