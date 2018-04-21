<?php

namespace Tests\Feature\Platform;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Url;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UrlTest extends TestCase
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
        $this->get(route('platform.urls.index'))
            ->assertStatus(200);
        create(Url::class, ['user_id' => $this->user->id]);
        $this->get(route('platform.urls.index'))
            ->assertStatus(200);
    }

    /** @test */
    function create_page_loads_successfully()
    {
        $this->get(route('platform.urls.create'))
            ->assertStatus(200);
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
        $organization->users()->attach($this->user, ['role_id' => OrganizationUser::ROLE_MEMBER]);
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
}
