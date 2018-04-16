<?php

namespace Tests\Feature\Platform;

use App\Models\Domain;
use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function domains_can_be_listed()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.domains.index'))
            ->assertStatus(200);
    }

    /** @test */
    function creation_page_loads_successfully()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.domains.create'))
            ->assertStatus(200);
    }

    /** @test */
    function domains_can_be_created()
    {
        $this->signInAdmin();
        $domain = make(Domain::class);

        $this->get(route('platform.admin.domains.create'));
        $this->post(route('platform.admin.domains.store'), [
            'url' => $domain->url,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($domain->getTable(), [
            'url' => $domain->url,
        ]);
    }

    /** @test */
    function show_page_redirects_to_edit_page()
    {
        $this->signInAdmin();
        $domain = create(Domain::class);

        $this->get(route('platform.admin.domains.show', $domain))
            ->assertRedirect(route('platform.admin.domains.edit', $domain));
    }

    /** @test */
    function edit_page_loads_successfully()
    {
        $this->signInAdmin();
        $domain = create(Domain::class);

        $this->get(route('platform.admin.domains.edit', $domain))
            ->assertStatus(200);
    }

    /** @test */
    function domains_can_be_edited()
    {
        $this->signInAdmin();
        $domain = create(Domain::class);
        $template = make(Domain::class);

        $this->get(route('platform.admin.domains.edit', $domain));
        $this->put(route('platform.admin.domains.update', $domain), [
            'url' => $domain->url,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($template->getTable(), [
            'id' => $domain->id,
            'url' => $domain->url,
        ]);
    }

    /** @test */
    function domains_can_be_deleted()
    {
        $this->signInAdmin();
        $domain = create(Domain::class);

        $this->get(route('platform.admin.domains.index'));
        $this->delete(route('platform.admin.domains.destroy', $domain))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing($domain->getTable(), [
            'id' => $domain->id,
        ]);
    }

    /** @test */
    function domains_cannot_be_deleted_with_urls_attached()
    {
        $this->signInAdmin();
        $domain = create(Domain::class);
        create(Url::class, ['domain_id' => $domain->id]);

        $this->get(route('platform.admin.domains.index'));
        $this->delete(route('platform.admin.domains.destroy', $domain))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas($domain->getTable(), [
            'id' => $domain->id,
        ]);
    }
}
