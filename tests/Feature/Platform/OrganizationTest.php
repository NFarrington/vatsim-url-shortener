<?php

namespace Tests\Feature\Platform;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\Url;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\TestCase;
use Tests\Traits\RefreshDatabase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signIn();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_loads_successfully()
    {
        $this->get(route('platform.organizations.index'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_loads_successfully_for_users_with_an_organization()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);
        $this->get(route('platform.organizations.index'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['id'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['name'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['updatedAt'])]
    function index_page_sorts_by_every_sortable_column($sort)
    {
        $organization = create(Organization::class);
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);
        $this->get(route('platform.organizations.index', ['sort' => $sort, 'direction' => 'desc']))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function index_page_rejects_an_unknown_sort_column()
    {
        $this->withExceptionHandling();

        $this->get(route('platform.organizations.index', ['sort' => 'doesNotExist']))
            ->assertSessionHasErrors('sort');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function create_page_loads_successfully()
    {
        $this->get(route('platform.organizations.create'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_can_be_created()
    {
        $organization = make(Organization::class);

        $this->get(route('platform.organizations.create'));
        $this->post(route('platform.organizations.store'), [
            'name' => $organization->getName(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($organization))->getTableName(), [
            'name' => $organization->getName(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function show_organization_redirects_to_edit()
    {
        $organization = create(Organization::class);
        $this->get(route('platform.organizations.show', $organization))
            ->assertRedirect(route('platform.organizations.edit', $organization));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function edit_page_loads_successfully()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);
        $this->get(route('platform.organizations.edit', $organization))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_can_be_edited()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        $this->refreshAllEntities();

        $template = make(Organization::class);

        $this->get(route('platform.organizations.edit', $organization));
        $this->put(route('platform.organizations.update', $organization), [
            'name' => $template->getName(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($organization))->getTableName(), [
            'name' => $template->getName(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_can_be_deleted()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        EntityManager::refresh($organization);

        $this->get(route('platform.organizations.edit', $organization));
        $this->delete(route('platform.organizations.destroy', $organization))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSoftDeleted(EntityManager::getClassMetadata(get_class($organization))->getTableName(), [
            'id' => $organization->getId(),
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function organization_with_urls_cannot_be_deleted()
    {
        $organization = entity(Organization::class)->states('prefix')->create();
        create(OrganizationUser::class, ['user' => $this->user, 'organization' => $organization, 'roleId' => OrganizationUser::ROLE_OWNER]);
        entity(Url::class)->states('org')->create(['organization' => $organization]);
        EntityManager::clear();

        $this->get(route('platform.organizations.edit', $organization));
        $this->delete(route('platform.organizations.destroy', $organization))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($organization))->getTableName(), [
            'id' => $organization->getId(),
            'deleted_at' => null,
        ]);
    }
}
