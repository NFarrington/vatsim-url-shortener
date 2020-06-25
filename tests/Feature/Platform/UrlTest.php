<?php

namespace Tests\Feature\Platform;

use App\Models\Domain;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Url;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
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
    function index_page_loads_successfully()
    {
        $this->get(route('platform.urls.index'))
            ->assertStatus(200);

        $url = create(Url::class, ['user_id' => $this->user->id]);
        $this->get(route('platform.urls.index'))
            ->assertStatus(200)
            ->assertSee($url->url);

        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);
        $url = factory(Url::class)->states('org')->create(['organization_id' => $organization->id]);
        $this->get(route('platform.urls.index'))
            ->assertStatus(200)
            ->assertSee($url->url);
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
        $domain = factory(Domain::class)->state('private')->create();
        $organization = create(Organization::class);
        $organization->domains()->attach($domain);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);

        $this->get(route('platform.urls.create'))
            ->assertStatus(200)
            ->assertSee($domain->url);
    }

    /** @test */
    function user_can_create_new_url()
    {
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => null,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($url->getTable(), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
        ]);
    }

    /** @test */
    function user_can_create_new_url_in_an_organization()
    {
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($url->getTable(), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ]);
    }

    /** @test */
    function user_can_create_new_url_with_a_prefix()
    {
        $organization = factory(Organization::class)->states('prefix')->create();
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->domain_id,
            'prefix' => $organization->prefix,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($url->getTable(), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ]);
    }

    /** @test */
    function user_cannot_create_new_url_with_an_invalid_prefix()
    {
        $this->expectException(ValidationException::class);

        $organization = factory(Organization::class)->states('prefix')->create();
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->domain_id,
            'prefix' => $organization->prefix.'1',
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ])->assertRedirect()
            ->assertRedirect();
        $this->assertDatabaseMissing($url->getTable(), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ]);
    }

    /** @test */
    function user_cannot_create_new_url_with_a_mismatched_prefix_and_organization()
    {
        $this->expectException(ValidationException::class);

        $organization = factory(Organization::class)->states('prefix')->create();
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->domain_id,
            'prefix' => $organization->prefix,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => null,
        ])->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseMissing($url->getTable(), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ]);
    }

    /** @test */
    function show_page_redirects_to_edit_page()
    {
        $url = create(Url::class, ['user_id' => $this->user->id]);

        $this->get(route('platform.urls.show', $url))
            ->assertRedirect(route('platform.urls.edit', $url));
    }

    /** @test */
    function edit_page_loads_successfully()
    {
        $url = create(Url::class, ['user_id' => $this->user]);

        $this->get(route('platform.urls.edit', $url))
            ->assertStatus(200);
    }

    /** @test */
    function user_can_edit_url_owned_by_user()
    {
        $url = create(Url::class, ['user_id' => $this->user->id]);
        $template = make(Url::class);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $template->redirect_url,
            'organization_id' => null,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($url->getTable(), [
            'redirect_url' => $template->redirect_url,
            'organization_id' => null,
        ]);
    }

    /** @test */
    function user_can_edit_url_owned_by_organization()
    {
        $template = make(Url::class);
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);
        $url = factory(Url::class)->states('org')->create(['organization_id' => $organization->id]);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $template->redirect_url,
            'organization_id' => $organization->id,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($url->getTable(), [
            'redirect_url' => $template->redirect_url,
            'organization_id' => $organization->id,
        ]);
    }

    /** @test */
    function user_can_move_url_into_an_organization()
    {
        $url = create(Url::class, ['user_id' => $this->user->id]);
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($url->getTable(), [
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ]);
    }

    /** @test */
    function user_can_move_url_to_a_different_organization()
    {
        $template = make(Url::class);
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MANAGER]);
        $url = factory(Url::class)->states('org')->create(['organization_id' => $organization->id]);
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $template->redirect_url,
            'organization_id' => $organization->id,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($url->getTable(), [
            'redirect_url' => $template->redirect_url,
            'organization_id' => $organization->id,
        ]);
    }

    /** @test */
    function user_cannot_move_url_with_prefix()
    {
        $this->expectException(AuthorizationException::class);

        $template = make(Url::class);
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MANAGER]);
        $url = factory(Url::class)->states('org')->create(['organization_id' => $organization->id, 'prefix' => true]);
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);

        $this->get(route('platform.urls.edit', $url));
        $this->put(route('platform.urls.update', $url), [
            'redirect_url' => $template->redirect_url,
            'organization_id' => $organization->id,
        ])->assertRedirect()
            ->assertForbidden();
        $this->assertDatabaseHas($url->getTable(), [
            'redirect_url' => $url->redirect_url,
            'organization_id' => $url->organization_id,
        ]);
    }

    /** @test */
    function user_can_delete_url_owned_by_user()
    {
        $url = create(Url::class, ['user_id' => $this->user->id]);
        $this->get(route('platform.urls.index'));
        $this->delete(route('platform.urls.destroy', $url))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing($url->getTable(), ['id' => $url->id, 'deleted_at' => null]);
    }

    /** @test */
    function user_can_delete_url_owned_by_organization()
    {
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MANAGER]);
        $url = factory(Url::class)->states('org')->create(['organization_id' => $organization->id]);

        $this->get(route('platform.urls.index'));
        $this->delete(route('platform.urls.destroy', $url))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing($url->getTable(), ['id' => $url->id, 'deleted_at' => null]);
    }

    /** @test */
    function user_cannot_create_existing_url()
    {
        $this->expectException(ValidationException::class);

        $url = create(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->domain_id,
            'organization_id' => null,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
        ])->assertRedirect()
            ->assertSessionHasErrors('url');
    }

    /** @test */
    function user_cannot_create_url_in_another_organization()
    {
        $this->expectException(AuthorizationException::class);

        $organization = create(Organization::class);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ])->assertForbidden();
        $this->assertDatabaseMissing($url->getTable(), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
        ]);
    }

    /** @test */
    function user_cannot_delete_other_users_urls()
    {
        $this->expectException(AuthorizationException::class);

        $url = create(Url::class);
        $this->get(route('platform.urls.index'));
        $this->delete(route('platform.urls.destroy', $url))
            ->assertForbidden();
        $this->assertDatabaseHas($url->getTable(), ['id' => $url->id, 'deleted_at' => null]);
    }

    /** @test */
    function user_can_create_url_for_private_domain()
    {
        $domain = factory(Domain::class)->state('private')->create();
        $organization = create(Organization::class);
        $organization->domains()->attach($domain);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $domain->id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($url->getTable(), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ]);
    }

    /** @test */
    function user_cannot_create_url_for_private_domain_they_do_not_own()
    {
        $this->withExceptionHandling();
        $domain = factory(Domain::class)->state('private')->create();
        $organization = create(Organization::class);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $domain->id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => $organization->id,
        ])->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertDatabaseMissing($url->getTable(), [
            'domain_id' => $url->domain_id,
        ]);
    }

    /** @test */
    function user_cannot_create_url_with_mismatched_domain_and_organization()
    {
        $this->withExceptionHandling();
        $domain = factory(Domain::class)->state('private')->create();
        $organization = create(Organization::class);
        $organization->domains()->attach($domain);
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);
        $url = make(Url::class);

        $this->get(route('platform.urls.create'));
        $this->post(route('platform.urls.store'), [
            'domain_id' => $domain->id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
            'organization_id' => null,
        ])->assertRedirect()
            ->assertSessionHasErrors('organization_id');
        $this->assertDatabaseMissing($url->getTable(), [
            'domain_id' => $url->domain_id,
        ]);
    }
}
