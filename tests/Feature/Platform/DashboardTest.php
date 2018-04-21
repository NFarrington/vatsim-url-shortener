<?php

namespace Tests\Feature\Platform;

use App\Models\News;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function dashboard_page_loads_successfully()
    {
        $this->signIn();

        $this->get(route('platform.dashboard'))
            ->assertStatus(200);
        create(News::class);
        $this->get(route('platform.dashboard'))
            ->assertStatus(200);
    }

    /** @test */
    function dashboard_displays_news_posts()
    {
        $this->signIn();
        $news = create(News::class);

        $this->get(route('platform.dashboard'))
            ->assertSeeText($news->content);
    }

    /** @test */
    function platform_page_redirects_to_dashboard_page()
    {
        $this->signIn();
        $this->get(route('platform'))
            ->assertRedirect(route('platform.dashboard'));
    }
}
