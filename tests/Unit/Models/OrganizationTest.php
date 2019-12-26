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

/**
 * @covers \App\Models\Organization
 */
class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    private $organization;

    function setUp(): void
    {
        parent::setUp();
        $this->organization = create(Organization::class);
    }

    /** @test */
    function has_many_urls()
    {
        $expectedUrl1 = create(Url::class, ['organization_id' => $this->organization->id]);
        $expectedUrl2 = create(Url::class, ['organization_id' => $this->organization->id]);

        $actualUrlIds = $this->organization->urls->pluck('id');

        $this->assertEquals(2, $actualUrlIds->count());
        $this->assertContains($expectedUrl1->id, $actualUrlIds);
        $this->assertContains($expectedUrl2->id, $actualUrlIds);
    }

    /** @test */
    function belongs_to_many_owners()
    {
        $expectedOwner1 = $this->givenOrganizationHasUserWithRole(
            $this->organization, OrganizationUser::ROLE_OWNER);
        $expectedOwner2 = $this->givenOrganizationHasUserWithRole(
            $this->organization, OrganizationUser::ROLE_OWNER);

        $actualOwnerIds = $this->organization->owners->pluck('id');

        $this->assertEquals(2, $actualOwnerIds->count());
        $this->assertContains($expectedOwner1->id, $actualOwnerIds);
        $this->assertContains($expectedOwner2->id, $actualOwnerIds);
    }

    /** @test */
    function belongs_to_many_managers()
    {
        $expectedManager1 = $this->givenOrganizationHasUserWithRole(
            $this->organization, OrganizationUser::ROLE_MANAGER);
        $expectedManager2 = $this->givenOrganizationHasUserWithRole(
            $this->organization, OrganizationUser::ROLE_MANAGER);

        $actualManagerIds = $this->organization->managers->pluck('id');

        $this->assertEquals(2, $actualManagerIds->count());
        $this->assertContains($expectedManager1->id, $actualManagerIds);
        $this->assertContains($expectedManager2->id, $actualManagerIds);
    }

    /** @test */
    function belongs_to_many_members()
    {
        $expectedMember1 = $this->givenOrganizationHasUserWithRole(
            $this->organization, OrganizationUser::ROLE_MEMBER);
        $expectedMember2 = $this->givenOrganizationHasUserWithRole(
            $this->organization, OrganizationUser::ROLE_MEMBER);

        $actualMemberIds = $this->organization->members->pluck('id');

        $this->assertEquals(2, $actualMemberIds->count());
        $this->assertContains($expectedMember1->id, $actualMemberIds);
        $this->assertContains($expectedMember2->id, $actualMemberIds);
    }

    /** @test */
    function belongs_to_many_users()
    {
        $expectedUser1 = $this->givenOrganizationHasUserWithRole(
            $this->organization, OrganizationUser::ROLE_OWNER);
        $expectedUser2 = $this->givenOrganizationHasUserWithRole(
            $this->organization, OrganizationUser::ROLE_MEMBER);

        $actualUserIds = $this->organization->users->pluck('id');

        $this->assertEquals(2, $actualUserIds->count());
        $this->assertContains($expectedUser1->id, $actualUserIds);
        $this->assertContains($expectedUser2->id, $actualUserIds);
    }

    /** @test */
    function has_one_prefix_application()
    {
        $expectedApplication = create(OrganizationPrefixApplication::class, [
            'organization_id' => $this->organization->id,
        ]);

        $actualApplication = $this->organization->prefixApplication;

        $this->assertEquals($expectedApplication->id, $actualApplication->id);
    }

    /** @test */
    function does_not_have_deleted_prefix_applications()
    {
        create(OrganizationPrefixApplication::class, [
            'organization_id' => $this->organization->id,
            'deleted_at' => Carbon::now(),
        ]);

        $actualApplication = $this->organization->prefixApplication;

        $this->assertNull($actualApplication);
    }

    private function givenOrganizationHasUserWithRole(Organization $organization, int $role)
    {
        $user = create(User::class);

        $organization->users()->newPivot([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_id' => $role,
        ])->save();

        return $user;
    }
}
