<?php

namespace Tests\Feature\Platform;

use App\Entities\News;
use Illuminate\Auth\AuthenticationException;
use Tests\Traits\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function dashboard_page_loads_successfully()
    {
        $this->signIn();

        $this->get(route('platform.dashboard'))
            ->assertStatus(200);
        create(News::class);
        $this->get(route('platform.dashboard'))
            ->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function dashboard_displays_news_posts()
    {
        $this->signIn();
        $news = create(News::class);

        $this->get(route('platform.dashboard'))
            ->assertSeeText($news->getContent());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function platform_page_redirects_to_dashboard_page()
    {
        $this->signIn();
        $this->get(route('platform'))
            ->assertRedirect(route('platform.dashboard'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function unauthenticated_user_is_redirected_to_login_page()
    {
        $this->withExceptionHandling();

        $this->get(route('platform.dashboard'))
            ->assertRedirect(route('platform.login'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function news_markdown_does_not_render_unsafe_links()
    {
        $this->signIn();
        create(News::class, ['content' => '[click](javascript:alert(1))']);

        $this->get(route('platform.dashboard'))
            ->assertDontSee('javascript:', false);
    }
}
