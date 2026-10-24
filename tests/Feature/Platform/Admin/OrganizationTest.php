<?php

namespace Tests\Feature\Platform\Admin;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\Url;
use App\Entities\User;
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
    function organizations_can_be_sorted_by_prefix()
    {
        $this->signInAdmin();
        create(Organization::class, ['prefix' => 'bbb']);
        create(Organization::class, ['prefix' => 'ccc']);
        create(Organization::class, ['prefix' => 'aaa']);

        $this->get(route('platform.admin.organizations.index', ['sort' => 'prefix', 'direction' => 'asc']))
            ->assertStatus(200)
            ->assertSeeInOrder(['aaa', 'bbb', 'ccc']);
        $this->get(route('platform.admin.organizations.index', ['sort' => 'prefix', 'direction' => 'desc']))
            ->assertStatus(200)
            ->assertSeeInOrder(['ccc', 'bbb', 'aaa']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organizations_without_a_prefix_are_sorted_last()
    {
        $this->signInAdmin();
        create(Organization::class, ['name' => 'Organization One', 'prefix' => 'aaa']);
        create(Organization::class, ['name' => 'Organization Two']);
        create(Organization::class, ['name' => 'Organization Three', 'prefix' => 'bbb']);

        $this->get(route('platform.admin.organizations.index', ['sort' => 'prefix', 'direction' => 'asc']))
            ->assertStatus(200)
            ->assertSeeInOrder(['Organization One', 'Organization Three', 'Organization Two']);
        $this->get(route('platform.admin.organizations.index', ['sort' => 'prefix', 'direction' => 'desc']))
            ->assertStatus(200)
            ->assertSeeInOrder(['Organization Three', 'Organization One', 'Organization Two']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith([['sort' => 'name']])]
    #[\PHPUnit\Framework\Attributes\TestWith([['sort' => 'id,u.redirectUrl']])]
    #[\PHPUnit\Framework\Attributes\TestWith([['sort' => '../etc']])]
    #[\PHPUnit\Framework\Attributes\TestWith([['sort' => ['id']]])]
    #[\PHPUnit\Framework\Attributes\TestWith([['q' => ['quokka']]])]
    #[\PHPUnit\Framework\Attributes\TestWith([['direction' => 'sideways']])]
    #[\PHPUnit\Framework\Attributes\TestWith([['direction' => 'desc, u.id']])]
    #[\PHPUnit\Framework\Attributes\TestWith([['sort' => 'doesNotExist', 'direction' => 'sideways']])]
    function organizations_list_ignores_an_invalid_sort_or_direction($query)
    {
        $this->withExceptionHandling();
        $this->signInAdmin();
        create(Organization::class, ['prefix' => 'bbb']);
        create(Organization::class, ['prefix' => 'ccc']);
        create(Organization::class, ['prefix' => 'aaa']);

        // The default order is by ID.
        $this->get(route('platform.admin.organizations.index', $query))
            ->assertStatus(200)
            ->assertSessionHasNoErrors()
            ->assertSeeInOrder(['bbb', 'ccc', 'aaa']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organizations_can_be_searched_by_name_or_prefix()
    {
        $this->signInAdmin();
        $organization = create(Organization::class, ['name' => 'Quokka vACC', 'prefix' => 'qkk']);
        $other = create(Organization::class, ['name' => 'Other vACC', 'prefix' => 'oth']);

        $this->get(route('platform.admin.organizations.index', ['q' => 'QUOKKA']))
            ->assertStatus(200)
            ->assertSee($organization->getName())
            ->assertDontSee($other->getName());
        $this->get(route('platform.admin.organizations.index', ['q' => 'qk']))
            ->assertStatus(200)
            ->assertSee($organization->getName())
            ->assertDontSee($other->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organizations_can_be_searched_by_id()
    {
        $this->signInAdmin();
        $organization = create(Organization::class, ['name' => 'Quokka vACC']);
        $other = create(Organization::class, ['name' => 'Other vACC']);

        $this->get(route('platform.admin.organizations.index', ['q' => (string) $organization->getId()]))
            ->assertStatus(200)
            ->assertSee($organization->getName())
            ->assertDontSee($other->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organizations_can_be_searched_by_member_name_or_cid()
    {
        $this->signInAdmin();
        $member = create(User::class, ['firstName' => 'Zebedee', 'lastName' => 'Smith']);
        $organization = create(Organization::class, ['name' => 'Quokka vACC']);
        create(OrganizationUser::class, ['organization' => $organization, 'user' => $member]);
        create(OrganizationUser::class, ['organization' => $organization]);
        $other = create(Organization::class, ['name' => 'Other vACC']);
        create(OrganizationUser::class, ['organization' => $other]);

        $this->get(route('platform.admin.organizations.index', ['q' => 'zebedee smith']))
            ->assertStatus(200)
            ->assertSee($organization->getName())
            ->assertDontSee($other->getName());
        $this->get(route('platform.admin.organizations.index', ['q' => (string) $member->getId()]))
            ->assertStatus(200)
            ->assertSee($organization->getName())
            ->assertDontSee($other->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_search_ignores_removed_members()
    {
        $this->signInAdmin();
        $member = create(User::class, ['firstName' => 'Zebedee', 'lastName' => 'Smith']);
        $organization = create(Organization::class, ['name' => 'Quokka vACC']);
        $organizationUser = create(OrganizationUser::class, ['organization' => $organization, 'user' => $member]);
        EntityManager::remove($organizationUser);
        EntityManager::flush();

        $this->get(route('platform.admin.organizations.index', ['q' => 'zebedee']))
            ->assertStatus(200)
            ->assertDontSee($organization->getName())
            ->assertSee('Nothing to show.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_search_lists_each_organization_once()
    {
        $this->signInAdmin();
        $organization = create(Organization::class, ['name' => 'Quokka vACC']);
        create(OrganizationUser::class, ['organization' => $organization, 'user' => create(User::class, ['firstName' => 'Zebedee'])]);
        create(OrganizationUser::class, ['organization' => $organization, 'user' => create(User::class, ['firstName' => 'Zebedee'])]);

        $response = $this->get(route('platform.admin.organizations.index', ['q' => 'zebedee']))
            ->assertStatus(200);
        $this->assertSame(1, substr_count($response->getContent(), route('platform.admin.organizations.show', $organization).'"'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['%'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['_'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['!'])]
    function organization_search_treats_wildcards_literally($search)
    {
        $this->signInAdmin();
        $organization = create(Organization::class, ['name' => 'Quokka vACC']);

        $this->get(route('platform.admin.organizations.index', ['q' => $search]))
            ->assertStatus(200)
            ->assertDontSee($organization->getName())
            ->assertSee('Nothing to show.');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_search_and_sort_are_kept_together()
    {
        $this->signInAdmin();
        create(Organization::class, ['name' => 'Quokka vACC']);

        $this->get(route('platform.admin.organizations.index', ['q' => 'quokka']))
            ->assertStatus(200)
            ->assertSee('q=quokka', false);
        $this->get(route('platform.admin.organizations.index', ['q' => 'quokka', 'sort' => 'prefix', 'direction' => 'desc']))
            ->assertStatus(200)
            ->assertSee('<input type="hidden" name="sort" value="prefix">', false)
            ->assertSee('<input type="hidden" name="direction" value="desc">', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_list_shows_member_and_url_counts_without_loading_them()
    {
        $this->signInAdmin();
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['organization' => $organization], 3);
        create(Url::class, ['organization' => $organization], 2);
        EntityManager::clear();

        $this->get(route('platform.admin.organizations.index'))
            ->assertStatus(200)
            ->assertSeeInOrder([$organization->getName(), '<td>3</td>', '<td>2</td>'], false);

        $listedOrganization = EntityManager::find(Organization::class, $organization->getId());
        $metadata = EntityManager::getClassMetadata(Organization::class);
        $this->assertFalse($metadata->getFieldValue($listedOrganization, 'organizationUsers')->isInitialized());
        $this->assertFalse($metadata->getFieldValue($listedOrganization, 'urls')->isInitialized());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_urls_can_be_viewed()
    {
        $this->signInAdmin();
        $organization = create(Organization::class);
        $url = create(Url::class, ['organization' => $organization]);
        $otherUrl = create(Url::class);
        EntityManager::clear();

        $this->get(route('platform.admin.organizations.show', $organization))
            ->assertStatus(200)
            ->assertSee($url->getRedirectUrl())
            ->assertDontSee($otherUrl->getRedirectUrl());
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
