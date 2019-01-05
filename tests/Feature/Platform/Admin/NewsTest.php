<?php

namespace Tests\Feature\Platform;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function news_articles_can_be_listed()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.news.index'))
            ->assertStatus(200);
        create(News::class);
        $this->get(route('platform.admin.news.index'))
            ->assertStatus(200);
    }

    /** @test */
    function creation_page_loads_successfully()
    {
        $this->signInAdmin();

        $this->get(route('platform.admin.news.create'))
            ->assertStatus(200);
    }

    /** @test */
    function news_can_be_created()
    {
        $this->signInAdmin();
        $news = make(News::class);

        $this->get(route('platform.admin.news.create'));
        $this->post(route('platform.admin.news.store'), [
            'title' => $news->title,
            'content' => $news->content,
            'published' => 1,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($news->getTable(), [
            'title' => $news->title,
            'content' => $news->content,
            'published' => 1,
        ]);
    }

    /** @test */
    function show_page_redirects_to_edit_page()
    {
        $this->signInAdmin();
        $news = create(News::class);

        $this->get(route('platform.admin.news.show', $news))
            ->assertRedirect(route('platform.admin.news.edit', $news));
    }

    /** @test */
    function edit_page_loads_successfully()
    {
        $this->signInAdmin();
        $news = create(News::class);

        $this->get(route('platform.admin.news.edit', $news))
            ->assertStatus(200);
    }

    /** @test */
    function news_can_be_edited()
    {
        $this->signInAdmin();
        $news = create(News::class);
        $template = make(News::class);

        $this->get(route('platform.admin.news.edit', $news));
        $this->put(route('platform.admin.news.update', $news), [
            'title' => $template->title,
            'content' => $template->content,
        ])->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas($template->getTable(), [
            'id' => $news->id,
            'title' => $template->title,
            'content' => $template->content,
            'published' => 0,
        ]);
    }

    /** @test */
    function news_can_be_deleted()
    {
        $this->signInAdmin();
        $news = create(News::class);

        $this->get(route('platform.admin.news.index'));
        $this->delete(route('platform.admin.news.destroy', $news))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing($news->getTable(), [
            'id' => $news->id,
        ]);
    }
}
