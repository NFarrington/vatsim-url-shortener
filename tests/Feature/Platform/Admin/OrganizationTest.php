<?php

namespace Tests\Feature\Platform\Admin;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function organizations_can_be_listed()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.organizations.index'))
            ->assertStatus(200);
        $organization = create(Organization::class);
        $this->get(route('platform.admin.organizations.index'))
            ->assertStatus(200)
            ->assertSee($organization->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_list_shows_member_counts_without_loading_members()
    {
        $this->signInAdmin();
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['organization' => $organization], 3);
        EntityManager::clear();

        $this->get(route('platform.admin.organizations.index'))
            ->assertStatus(200)
            ->assertSeeInOrder([$organization->getName(), '<td>3</td>'], false);

        $listedOrganization = EntityManager::find(Organization::class, $organization->getId());
        $this->assertFalse(
            EntityManager::getClassMetadata(Organization::class)
                ->getFieldValue($listedOrganization, 'organizationUsers')
                ->isInitialized()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_members_can_be_viewed()
    {
        $this->signInAdmin();
        $organizationUser = create(OrganizationUser::class, ['roleId' => OrganizationUser::ROLE_MANAGER]);
        $organization = $organizationUser->getOrganization();
        EntityManager::clear();

        $this->get(route('platform.admin.organizations.show', $organization))
            ->assertStatus(200)
            ->assertSee($organization->getName())
            ->assertSee($organizationUser->getUser()->getFullName())
            ->assertSee('Manager');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_pages_are_inaccessible_to_non_admins()
    {
        $this->withExceptionHandling();

        $this->signIn();
        $organization = create(Organization::class);

        $this->get(route('platform.admin.organizations.index'))
            ->assertForbidden();
        $this->get(route('platform.admin.organizations.show', $organization))
            ->assertForbidden();
    }
}
