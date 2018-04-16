<?php

namespace Tests\Feature\Platform;

use App\Models\Domain;
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
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($url->getTable(), [
            'domain_id' => $url->domain_id,
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
        ]);
    }

    /** @test */
    function user_can_delete_url()
    {
        $url = create(Url::class, ['user_id' => $this->user->id]);
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
            'url' => $url->url,
            'redirect_url' => $url->redirect_url,
        ])->assertRedirect()
            ->assertSessionHasErrors('url');
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
