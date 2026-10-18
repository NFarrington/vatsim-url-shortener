<?php

namespace Tests\Feature\Platform\Admin;

use App\Entities\News;
use Tests\Traits\RefreshDatabase;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function news_articles_can_be_listed()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.news.index'))
            ->assertStatus(200);
        create(News::class);
        $this->get(route('platform.admin.news.index'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function creation_page_loads_successfully()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.news.create'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function news_can_be_created()
    {
        $this->signInAdmin();
        $news = make(News::class);

        $this->get(route('platform.admin.news.create'));
        $this->post(route('platform.admin.news.store'), [
            'title' => $news->getTitle(),
            'content' => $news->getContent(),
            'published' => 1,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($news))->getTableName(), [
            'title' => $news->getTitle(),
            'content' => $news->getContent(),
            'published' => 1,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function show_page_redirects_to_edit_page()
    {
        $this->signInAdmin();
        $news = create(News::class);

        $this->get(route('platform.admin.news.show', $news))
            ->assertRedirect(route('platform.admin.news.edit', $news));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function edit_page_loads_successfully()
    {
        $this->signInAdmin();
        $news = create(News::class);

        $this->get(route('platform.admin.news.edit', $news))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function news_can_be_edited()
    {
        $this->signInAdmin();
        $news = create(News::class);
        $template = make(News::class);

        $this->get(route('platform.admin.news.edit', $news));
        $this->put(route('platform.admin.news.update', $news), [
            'title' => $template->getTitle(),
            'content' => $template->getContent(),
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas(EntityManager::getClassMetadata(get_class($template))->getTableName(), [
            'id' => $news->getId(),
            'title' => $template->getTitle(),
            'content' => $template->getContent(),
            'published' => 0,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function news_can_be_deleted()
    {
        $this->signInAdmin();
        $news = create(News::class);
        $newsId = $news->getId();

        $this->get(route('platform.admin.news.index'));
        $this->delete(route('platform.admin.news.destroy', $news))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(News::class)->getTableName(), [
            'id' => $newsId,
        ]);
    }
}
