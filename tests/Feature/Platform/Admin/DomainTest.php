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

    #[\PHPUnit\Framework\Attributes\Test]
    function domains_can_be_listed()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.domains.index'))
            ->assertStatus(200);
        create(Domain::class);
        $this->get(route('platform.admin.domains.index'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function creation_page_loads_successfully()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.domains.create'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    function show_page_redirects_to_edit_page()
    {
        $this->signInAdmin();
        $domain = create(Domain::class);

        $this->get(route('platform.admin.domains.show', $domain))
            ->assertRedirect(route('platform.admin.domains.edit', $domain));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function edit_page_loads_successfully()
    {
        $this->signInAdmin();
        $domain = create(Domain::class);

        $this->get(route('platform.admin.domains.edit', $domain))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\TestWith(['javascript://%0Aalert(document.domain)//'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['ftp://example.com/'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['https://example.com'])]
    #[\PHPUnit\Framework\Attributes\TestWith(['https://example.com/path/'])]
    function domains_cannot_be_created_with_an_invalid_url($url)
    {
        $this->withExceptionHandling();
        $this->signInAdmin();

        $this->post(route('platform.admin.domains.store'), ['url' => $url, 'public' => 1])
            ->assertSessionHasErrors('url');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(Domain::class)->getTableName(), ['url' => $url]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function domains_cannot_be_edited_to_have_an_invalid_url()
    {
        $this->withExceptionHandling();
        $this->signInAdmin();
        $domain = create(Domain::class);

        $this->put(route('platform.admin.domains.update', $domain), ['url' => 'javascript://%0Aalert(1)//', 'public' => 1])
            ->assertSessionHasErrors('url');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function domains_can_be_edited_without_changing_public()
    {
        $this->signInAdmin();
        $domain = create(Domain::class, ['public' => true]);

        $this->put(route('platform.admin.domains.update', $domain), ['url' => 'https://changed.example/'])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertTrue($domain->isPublic());
        $this->assertSame('https://changed.example/', $domain->getUrl());
    }
}
