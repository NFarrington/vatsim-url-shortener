<?php

namespace Tests\Feature\Platform;

use App\Entities\Domain;
use App\Entities\DomainOrganization;
use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\Url;
use Illuminate\Auth\Access\AuthorizationException;
use Tests\Traits\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    function index_page_loads()
    {
        $this->get(route('platform.urls.index'))
            ->assertStatus(200);
    }

    /** @test */
    function index_page_loads_personal_urls()
    {
        $url = create(Url::class, ['user' => $this->user]);
        $this->get(route('platform.urls.index'))
            ->assertStatus(200)
            ->assertSee($url->getUrl());
    }

    /** @test */
    function index_page_loads_organization_urls()
    {
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        $url = entity(Url::class)->states('org')->create(['organization' => $organization]);
        $this->get(route('platform.urls.index'))
            ->assertStatus(200)
            ->assertSee($url->getUrl());
    }

    /** @test */
    function index_page_loads_global_urls()
    {
        $url = create(Url::class, [
            'user' => null,
            'organization' => null,
        ]);
        $this->get(route('platform.urls.index'))
            ->assertStatus(200)
            ->assertSee('Public URLs')
            ->assertSee($url->getUrl());
    }

    /** @test */
    function index_page_doesnt_have_public_urls_when_none_exist()
    {
        $this->get(route('platform.urls.index'))
            ->assertStatus(200)
            ->assertDontSee('Public URLs');
    }

    /** @test */
    function create_page_loads_successfully()
    {
        $this->get(route('platform.urls.create'))
            ->assertStatus(200);
    }

    /** @test */
    function create_page_shows_private_domains()
    {
        $domain = entity(Domain::class)->states('private')->create();
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        create(DomainOrganization::class, ['domain' => $domain, 'organization' => $organization]);
        EntityManager::refresh($domain);
        EntityManager::refresh($organization);

        $this->get(route('platform.urls.create'))
            ->assertStatus(200)
            ->assertSee($domain->getUrl());
    }

    /** @test */
    function user_can_create_new_url()
    {
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => null,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
        ]);
    }

    /** @test */
    function user_can_create_new_url_in_an_organization()
    {
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ]);
    }

    /** @test */
    function user_can_create_new_url_with_a_prefix()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        EntityManager::refresh($this->user);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'prefix' => $organization->getPrefix(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'prefix' => true,
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ]);
    }

    /** @test */
    function user_cannot_create_new_url_with_an_invalid_prefix()
    {
        $this->withExceptionHandling();

        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'prefix' => $organization->getPrefix().'1',
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertRedirect()
            ->assertSessionHasErrors();
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ]);
    }

    /** @test */
    function user_cannot_create_new_url_with_a_mismatched_prefix_and_organization()
    {
        $this->withExceptionHandling();

        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'prefix' => $organization->getPrefix(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => null,
        ])->assertRedirect()
            ->assertSessionHasErrors();
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ]);
    }

    /** @test */
    function show_page_redirects_to_edit_page()
    {
        $url = create(Url::class, ['user' => $this->user]);

        $this->get(route('platform.urls.show', $url))
            ->assertRedirect(route('platform.urls.edit', $url));
    }

    /** @test */
    function edit_page_loads_successfully()
    {
        $url = create(Url::class, ['user' => $this->user]);

        $this->get(route('platform.urls.edit', $url))
            ->assertStatus(200);
    }

    /** @test */
    function user_can_edit_url_owned_by_user()
    {
        $url = create(Url::class, ['user' => $this->user]);
        $template = make(Url::class);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $template->getRedirectUrl(),
            'organization_id' => null,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'redirect_url' => $template->getRedirectUrl(),
            'organization_id' => null,
        ]);
    }

    /** @test */
    function user_can_edit_url_owned_by_organization()
    {
        $template = make(Url::class);
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        EntityManager::refresh($this->user);
        $url = entity(Url::class)->states('org')->create(['organization' => $organization]);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $template->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'redirect_url' => $template->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ]);
    }

    /** @test */
    function user_can_move_url_into_an_organization()
    {
        $url = create(Url::class, ['user' => $this->user]);
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ]);
    }

    /** @test */
    function user_can_move_url_to_a_different_organization()
    {
        $template = make(Url::class);
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MANAGER]);
        EntityManager::refresh($organization);
        $url = entity(Url::class)->states('org')->create(['organization' => $organization]);
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        EntityManager::refresh($this->user);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $template->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'redirect_url' => $template->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ]);
    }

    /** @test */
    function user_cannot_move_url_with_prefix()
    {
        $this->withExceptionHandling();

        $template = make(Url::class);
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MANAGER]);
        EntityManager::refresh($organization);
        $url = entity(Url::class)->states('org')->create(['organization' => $organization, 'prefix' => true]);
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $template->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertForbidden();
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $url->getOrganization()->getId(),
        ]);
    }

    /** @test */
    function user_can_delete_url_owned_by_user()
    {
        $url = create(Url::class, ['user' => $this->user]);
        $this->get(route('platform.urls.index'));
        $this->delete(route('platform.urls.destroy', $url))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Url::class)->getTableName(), ['id' => $url->getId(), 'deleted_at' => null]);
    }

    /** @test */
    function user_can_delete_url_owned_by_organization()
    {
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MANAGER]);
        EntityManager::refresh($organization);
        EntityManager::refresh($this->user);
        $url = entity(Url::class)->states('org')->create(['organization' => $organization]);

        $this->get(route('platform.urls.index'));
        $this->delete(route('platform.urls.destroy', $url))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Url::class)->getTableName(),
            ['id' => $url->getId(), 'deleted_at' => null]);
    }

    /** @test */
    function user_cannot_create_existing_url()
    {
        $this->withExceptionHandling();

        $url = create(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'organization_id' => null,
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
        ])->assertRedirect()
            ->assertSessionHasErrors('url');
    }

    /** @test */
    function user_cannot_create_url_in_another_organization()
    {
        $this->withExceptionHandling();

        $organization = create(Organization::class);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertForbidden();
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
        ]);
    }

    /** @test */
    function user_cannot_delete_other_users_urls()
    {
        $this->withExceptionHandling();

        $url = create(Url::class);
        $this->get(route('platform.urls.index'));
        $this->delete(route('platform.urls.destroy', $url))
            ->assertForbidden();
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), ['id' => $url->getId(), 'deleted_at' => null]);
    }

    /** @test */
    function user_can_create_url_for_private_domain()
    {
        $domain = entity(Domain::class)->states('private')->create();
        $organization = create(Organization::class);
        create(DomainOrganization::class, ['domain' => $domain, 'organization' => $organization]);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        $this->refreshAllEntities();
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $domain->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ]);
    }

    /** @test */
    function user_cannot_create_url_for_private_domain_they_do_not_own()
    {
        $this->withExceptionHandling();
        $domain = entity(Domain::class)->states('private')->create();
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        EntityManager::refresh($this->user);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $domain->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'domain_id' => $url->getDomain()->getId(),
        ]);
    }

    /** @test */
    function user_cannot_create_url_with_mismatched_domain_and_organization()
    {
        $this->withExceptionHandling();
        $domain = entity(Domain::class)->states('private')->create();
        $organization = create(Organization::class);
        create(DomainOrganization::class, ['domain' => $domain, 'organization' => $organization]);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($this->user);
        EntityManager::clear();
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $domain->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => null,
        ])->assertRedirect()
            ->assertSessionHasErrors('organization_id');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'domain_id' => $url->getDomain()->getId(),
        ]);
    }
}
