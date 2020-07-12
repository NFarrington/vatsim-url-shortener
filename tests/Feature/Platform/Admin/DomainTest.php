<?php

namespace Tests\Feature\Platform\Admin;

use App\Entities\Domain;
use App\Entities\Url;
use Tests\Traits\RefreshDatabase;
use LaravelDoctrine\ORM\Facades\EntityManager;
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
        create(Domain::class);
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
            'url' => $domain->getUrl(),
            'public' => $domain->isPublic(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($domain))->getTableName(), [
            'url' => $domain->getUrl(),
            'public' => $domain->isPublic(),
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
            'url' => $domain->getUrl(),
            'public' => $domain->isPublic(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($template))->getTableName(), [
            'id' => $domain->getId(),
            'url' => $domain->getUrl(),
            'public' => $domain->isPublic(),
        ]);
    }

    /** @test */
    function domains_can_be_deleted()
    {
        $this->signInAdmin();
        $domain = create(Domain::class);
        $domainId = $domain->getId();

        $this->get(route('platform.admin.domains.index'));
        $this->delete(route('platform.admin.domains.destroy', $domain))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Domain::class)->getTableName(), [
            'id' => $domainId,
        ]);
    }

    /** @test */
    function domains_cannot_be_deleted_with_urls_attached()
    {
        $this->signInAdmin();
        $domain = create(Domain::class);
        $domain->addUrl(create(Url::class));

        $this->get(route('platform.admin.domains.index'));
        $this->delete(route('platform.admin.domains.destroy', $domain))
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($domain))->getTableName(), [
            'id' => $domain->getId(),
        ]);
    }
}
