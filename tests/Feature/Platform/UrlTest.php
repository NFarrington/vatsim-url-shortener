<?php

namespace Tests\Feature\Platform;

use App\Entities\Domain;
use App\Entities\DomainOrganization;
use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\Url;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Auth\Access\AuthorizationException;
use Tests\Traits\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_loads()
    {
        $this->get(route('platform.urls.index'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_loads_personal_urls()
    {
        $url = create(Url::class, ['user' => $this->user]);
        $this->get(route('platform.urls.index'))
            ->assertStatus(200)
            ->assertSee($url->getUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_loads_and_sorts_urls_by_full_url()
    {
        $url = create(Url::class, ['user' => $this->user]);
        $this->get(route('platform.urls.index', ['sort' => 'fullUrl', 'direction' => 'asc']))
            ->assertStatus(200)
            ->assertSee($url->getUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_loads_and_sorts_urls_by_redirect_url()
    {
        $url = create(Url::class, ['user' => $this->user]);
        $this->get(route('platform.urls.index', ['sort' => 'redirectUrl', 'direction' => 'asc']))
            ->assertStatus(200)
            ->assertSee($url->getUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_loads_public_urls()
    {
        $url = create(Url::class, [
            'public' => 1,
        ]);
        $this->get(route('platform.urls.index'))
            ->assertStatus(200)
            ->assertSee('Public URLs')
            ->assertSee($url->getUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_loads_urls_with_no_assigned_owner_as_public()
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

    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_doesnt_have_public_urls_when_none_exist()
    {
        $this->get(route('platform.urls.index'))
            ->assertStatus(200)
            ->assertDontSee('Public URLs');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function create_page_loads_successfully()
    {
        $this->get(route('platform.urls.create'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    function show_page_redirects_to_edit_page()
    {
        $url = create(Url::class, ['user' => $this->user]);

        $this->get(route('platform.urls.show', $url))
            ->assertRedirect(route('platform.urls.edit', $url));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function edit_page_loads_successfully()
    {
        $url = create(Url::class, ['user' => $this->user]);

        $this->get(route('platform.urls.edit', $url))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function edit_page_loads_for_unmovable_url_with_no_organization()
    {
        $domain = entity(Domain::class)->states('private')->create();
        $url = create(Url::class, ['user' => $this->user, 'domain' => $domain]);

        $this->get(route('platform.urls.edit', $url))
            ->assertStatus(200)
            ->assertSee('<option value="">None</option>', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_edit_url_with_a_prefix_owned_by_organization()
    {
        $template = make(Url::class);
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        EntityManager::refresh($this->user);
        $url = entity(Url::class)->states('org', 'prefix')->create(['organization' => $organization]);

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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_create_url_in_an_organization_they_are_not_a_member_of()
    {
        $this->withExceptionHandling();

        $organization = create(Organization::class);
        create(OrganizationUser::class, ['organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertForbidden();
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'url' => $url->getUrl(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_edit_url_owned_by_an_organization_they_are_not_a_member_of()
    {
        $this->withExceptionHandling();

        $template = make(Url::class);
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);
        $url = entity(Url::class)->states('org')->create(['organization' => $organization]);

        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $template->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertForbidden();
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'id' => $url->getId(),
            'redirect_url' => $url->getRedirectUrl(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_move_url_into_an_organization_they_are_not_a_member_of()
    {
        $this->withExceptionHandling();

        $url = create(Url::class, ['user' => $this->user]);
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertForbidden();
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'id' => $url->getId(),
            'organization_id' => null,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_delete_url_owned_by_user()
    {
        $url = create(Url::class, ['user' => $this->user]);
        $this->get(route('platform.urls.index'));
        $this->delete(route('platform.urls.destroy', $url))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Url::class)->getTableName(), ['id' => $url->getId(), 'deleted_at' => null]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_delete_other_users_urls()
    {
        $this->withExceptionHandling();

        $url = create(Url::class);
        $this->get(route('platform.urls.index'));
        $this->delete(route('platform.urls.destroy', $url))
            ->assertForbidden();
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), ['id' => $url->getId(), 'deleted_at' => null]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    function creating_a_url_writes_it_to_dynamodb()
    {
        $dynamoDb = $this->mockDynamoDb();
        $url = make(Url::class, ['url' => 'MixedCaseUrl']);

        $dynamoDb->expects('putItem')->with([
            'TableName' => 'TestUrlsTable',
            'Item' => [
                'Url' => ['S' => Str::lower($url->getDomain()->getUrl()).'mixedcaseurl'],
                'RedirectUrl' => ['S' => $url->getRedirectUrl()],
                'UpdatedAt' => ['S' => Carbon::now()->toIso8601ZuluString()],
            ],
        ]);

        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => null,
        ])->assertRedirect()
            ->assertSessionHas('success');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function creating_a_url_with_a_prefix_writes_it_to_dynamodb()
    {
        $dynamoDb = $this->mockDynamoDb();
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        EntityManager::refresh($this->user);
        $url = make(Url::class);

        $dynamoDb->expects('putItem')->with([
            'TableName' => 'TestUrlsTable',
            'Item' => [
                'Url' => ['S' => Str::lower($url->getDomain()->getUrl().$organization->getPrefix().'/'.$url->getUrl())],
                'RedirectUrl' => ['S' => $url->getRedirectUrl()],
                'UpdatedAt' => ['S' => Carbon::now()->toIso8601ZuluString()],
            ],
        ]);

        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'prefix' => $organization->getPrefix(),
            'url' => $url->getUrl(),
            'redirect_url' => $url->getRedirectUrl(),
            'organization_id' => $organization->getId(),
        ])->assertRedirect()
            ->assertSessionHas('success');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function updating_a_url_writes_it_to_dynamodb()
    {
        $dynamoDb = $this->mockDynamoDb();
        $url = create(Url::class, ['user' => $this->user, 'url' => 'MixedCaseUrl']);
        $template = make(Url::class);

        $dynamoDb->expects('putItem')->with([
            'TableName' => 'TestUrlsTable',
            'Item' => [
                'Url' => ['S' => Str::lower($url->getDomain()->getUrl()).'mixedcaseurl'],
                'RedirectUrl' => ['S' => $template->getRedirectUrl()],
                'UpdatedAt' => ['S' => Carbon::now()->toIso8601ZuluString()],
            ],
        ]);

        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $template->getRedirectUrl(),
            'organization_id' => null,
        ])->assertRedirect()
            ->assertSessionHas('success');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function deleting_a_url_deletes_it_from_dynamodb()
    {
        $dynamoDb = $this->mockDynamoDb();
        $url = create(Url::class, ['user' => $this->user, 'url' => 'MixedCaseUrl']);

        $dynamoDb->expects('deleteItem')->with([
            'TableName' => 'TestUrlsTable',
            'Key' => [
                'Url' => ['S' => Str::lower($url->getDomain()->getUrl()).'mixedcaseurl'],
            ],
        ]);

        $this->delete(route('platform.urls.destroy', $url))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function failed_requests_do_not_write_to_dynamodb()
    {
        $this->withExceptionHandling();
        $this->mockDynamoDb();
        $url = make(Url::class);

        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => 'not a url',
            'organization_id' => null,
        ])->assertSessionHasErrors();
    }

    /**
     * Replace the DynamoDB client with a strict mock, so any call not
     * explicitly expected by the test will fail it.
     */
    protected function mockDynamoDb(): MockInterface
    {
        Carbon::setTestNow(Carbon::now());
        config(['services.dynamodb.urls_table' => 'TestUrlsTable']);

        $mock = Mockery::mock(DynamoDbClient::class);
        $this->app->instance(DynamoDbClient::class, $mock);

        return $mock;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['ftp://example.com/file'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['file://localhost/etc/passwd'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['data://text/html,hello'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['ms-settings://privacy'])]
    function user_cannot_create_url_redirecting_to_a_non_http_url($redirectUrl)
    {
        $this->withExceptionHandling();

        $url = make(Url::class);

        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->getDomain()->getId(),
            'url' => $url->getUrl(),
            'redirect_url' => $redirectUrl,
            'organization_id' => null,
        ])->assertSessionHasErrors('redirect_url');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'url' => $url->getUrl(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_edit_url_to_redirect_to_a_non_http_url()
    {
        $this->withExceptionHandling();

        $url = create(Url::class, ['user' => $this->user]);

        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => 'file://localhost/etc/passwd',
            'organization_id' => null,
        ])->assertSessionHasErrors('redirect_url');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'id' => $url->getId(),
            'redirect_url' => $url->getRedirectUrl(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_create_existing_url_with_a_prefix()
    {
        $this->withExceptionHandling();

        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        $existing = create(Url::class, ['organization' => $organization, 'prefix' => true]);
        EntityManager::refresh($organization);
        EntityManager::refresh($this->user);

        $this->post(route('platform.urls.store'), [
            'domain_id' => $existing->getDomain()->getId(),
            'prefix' => $organization->getPrefix(),
            'url' => $existing->getUrl(),
            'redirect_url' => 'https://example.com',
            'organization_id' => $organization->getId(),
        ])->assertSessionHasErrors('url');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_create_url_with_a_prefix_matching_an_unprefixed_url()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        $existing = create(Url::class);
        EntityManager::refresh($organization);
        EntityManager::refresh($this->user);

        $this->post(route('platform.urls.store'), [
            'domain_id' => $existing->getDomain()->getId(),
            'prefix' => $organization->getPrefix(),
            'url' => $existing->getUrl(),
            'redirect_url' => 'https://example.com',
            'organization_id' => $organization->getId(),
        ])->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(Url::class)->getTableName(), [
            'url' => $existing->getUrl(),
            'prefix' => true,
            'organization_id' => $organization->getId(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_create_url_differing_only_in_case_from_an_existing_url()
    {
        $this->withExceptionHandling();

        $existing = create(Url::class, ['url' => 'MixedCase']);

        $this->post(route('platform.urls.store'), [
            'domain_id' => $existing->getDomain()->getId(),
            'url' => 'mixedcase',
            'redirect_url' => 'https://example.com',
            'organization_id' => null,
        ])->assertSessionHasErrors('url');
        $this->assertSame(1, $this->countUrlsIgnoringCase('mixedcase'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_cannot_create_prefixed_url_differing_only_in_case_from_an_existing_url()
    {
        $this->withExceptionHandling();

        $organization = $this->createOrganizationWithPrefix('pfx');
        create(Url::class, ['organization' => $organization, 'user' => null, 'prefix' => true, 'url' => 'MixedCase']);

        $this->post(route('platform.urls.store'), $this->prefixedUrlRequest($organization, 'mixedcase'))
            ->assertSessionHasErrors('url');
        $this->assertSame(1, $this->countUrlsIgnoringCase('mixedcase'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['pfx', 'taken'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['PFX', 'TAKEN'])]
    function user_cannot_create_url_another_organization_with_the_same_prefix_has($prefix, $url)
    {
        $this->withExceptionHandling();

        // Duplicate prefixes can't be approved now, but may already exist.
        $other = create(Organization::class, ['prefix' => 'pfx']);
        create(Url::class, ['organization' => $other, 'user' => null, 'prefix' => true, 'url' => 'taken']);
        $organization = $this->createOrganizationWithPrefix($prefix);

        $this->post(route('platform.urls.store'), $this->prefixedUrlRequest($organization, $url))
            ->assertSessionHasErrors('url');
        $this->assertSame(1, $this->countUrlsIgnoringCase('taken'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_create_url_another_organization_with_a_different_prefix_has()
    {
        $other = create(Organization::class, ['prefix' => 'other']);
        create(Url::class, ['organization' => $other, 'user' => null, 'prefix' => true, 'url' => 'shared']);
        $organization = $this->createOrganizationWithPrefix('pfx');

        $this->post(route('platform.urls.store'), $this->prefixedUrlRequest($organization, 'shared'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');
        $this->assertSame(2, $this->countUrlsIgnoringCase('shared'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function user_can_create_url_that_exists_on_another_domain()
    {
        $existing = create(Url::class, ['url' => 'shared']);
        $domain = create(Domain::class);

        $this->post(route('platform.urls.store'), [
            'domain_id' => $domain->getId(),
            'url' => 'SHARED',
            'redirect_url' => 'https://example.com',
            'organization_id' => null,
        ])->assertSessionHasNoErrors()
            ->assertSessionHas('success');
        $this->assertSame(2, $this->countUrlsIgnoringCase('shared'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['platform'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['Platform'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['ASSETS'])]
    function user_cannot_create_a_reserved_url($reserved)
    {
        $this->withExceptionHandling();

        $this->post(route('platform.urls.store'), [
            'domain_id' => create(Domain::class)->getId(),
            'url' => $reserved,
            'redirect_url' => 'https://example.com',
            'organization_id' => null,
        ])->assertSessionHasErrors('url');
        $this->assertSame(0, $this->countUrlsIgnoringCase($reserved));
    }

    protected function createOrganizationWithPrefix(string $prefix): Organization
    {
        $organization = create(Organization::class, ['prefix' => $prefix]);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_MEMBER]);
        EntityManager::refresh($organization);
        EntityManager::refresh($this->user);

        return $organization;
    }

    protected function prefixedUrlRequest(Organization $organization, string $url): array
    {
        return [
            'domain_id' => EntityManager::getRepository(Domain::class)->findAll()[0]->getId(),
            'prefix' => $organization->getPrefix(),
            'url' => $url,
            'redirect_url' => 'https://example.com',
            'organization_id' => $organization->getId(),
        ];
    }

    protected function countUrlsIgnoringCase(string $url): int
    {
        return (int) EntityManager::createQuery('SELECT COUNT(u.id) FROM '.Url::class.' u WHERE LOWER(u.url) = LOWER(:url)')
            ->setParameter('url', $url)
            ->getSingleScalarResult();
    }
}
