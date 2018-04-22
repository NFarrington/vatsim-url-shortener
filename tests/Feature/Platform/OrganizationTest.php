<?php

namespace Tests\Feature\Platform;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Url;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp()
    {
        parent::setUp();

        $this->signIn();
    }

    /** @test */
    function index_page_loads_successfully()
    {
        $this->get(route('platform.organizations.index'))
            ->assertStatus(200);
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_OWNER]);
        $this->get(route('platform.organizations.index'))
            ->assertStatus(200);
    }

    /** @test */
    function create_page_loads_successfully()
    {
        $this->get(route('platform.organizations.create'))
            ->assertStatus(200);
    }

    /** @test */
    function organization_can_be_created()
    {
        $organization = make(Organization::class);

        $this->get(route('platform.organizations.create'));
        $this->post(route('platform.organizations.store'), [
            'name' => $organization->name,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($organization->getTable(), [
            'name' => $organization->name,
        ]);
    }

    /** @test */
    function show_organization_redirects_to_edit()
    {
        $organization = create(Organization::class);
        $this->get(route('platform.organizations.show', $organization))
            ->assertRedirect(route('platform.organizations.edit', $organization));
    }

    /** @test */
    function edit_page_loads_successfully()
    {
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_OWNER]);
        $this->get(route('platform.organizations.edit', $organization))
            ->assertStatus(200);
    }

    /** @test */
    function organization_can_be_edited()
    {
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_OWNER]);
        $template = make(Organization::class);

        $this->get(route('platform.organizations.edit', $organization));
        $this->put(route('platform.organizations.update', $organization), [
            'name' => $template->name,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($organization->getTable(), [
            'name' => $template->name,
        ]);
    }

    /** @test */
    function organization_can_be_deleted()
    {
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_OWNER]);

        $this->get(route('platform.organizations.edit', $organization));
        $this->delete(route('platform.organizations.destroy', $organization))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSoftDeleted($organization->getTable(), [
            'id' => $organization->id,
        ]);
    }

    /** @test */
    function organization_with_urls_cannot_be_deleted()
    {
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_OWNER]);
        factory(Url::class)->states('org')->create(['organization_id' => $organization->id]);

        $this->get(route('platform.organizations.edit', $organization));
        $this->delete(route('platform.organizations.destroy', $organization))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas($organization->getTable(), [
            'id' => $organization->id,
            'deleted_at' => null,
        ]);
    }
}
