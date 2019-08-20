<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Url;
use App\Models\UrlAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function short_url_without_prefix_redirects_to_its_intended_target()
    {
        $domain = create(Domain::class, ['url' => config('app.url')]);
        $url = create(Url::class, ['domain_id' => $domain->id]);

        $this->get(route('short-url', $url->url))
            ->assertRedirect($url->redirect_url);
    }

    /** @test */
    function short_url_with_prefix_redirects_to_its_intended_target()
    {
        $domain = create(Domain::class, ['url' => config('app.url')]);
        $url = factory(Url::class)->states('org')
            ->create(['domain_id' => $domain->id, 'prefix' => true]);
        $url->organization->update(['prefix' => Str::random(3)]);

        $this->get($url->full_url)
            ->assertRedirect($url->redirect_url);
    }

    /** @test */
    function short_url_with_unknown_prefix_returns_404()
    {
        $this->expectException(NotFoundHttpException::class);

        $domain = create(Domain::class, ['url' => config('app.url')]);
        $url = factory(Url::class)->states('org')
            ->create(['domain_id' => $domain->id, 'prefix' => false]);

        $this->get($url->full_url.'/'.Str::random())
            ->assertNotFound();
    }

    /** @test */
    function short_url_returns_404_when_it_doesnt_exist()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->get(route('short-url', Str::random()))->assertNotFound();
    }

    /** @test */
    function short_url_returns_404_when_its_domain_doesnt_match()
    {
        $this->expectException(NotFoundHttpException::class);

        $url = create(Url::class);
        $this->get(route('short-url', $url->url))->assertNotFound();
    }

    /** @test */
    function short_url_clicks_are_logged()
    {
        $domain = create(Domain::class, ['url' => config('app.url')]);
        $url = create(Url::class, ['domain_id' => $domain->id]);

        $this->get(route('short-url', $url->url));
        $this->assertDatabaseHas((new UrlAnalytics())->getTable(), [
            'request_uri' => $url->url,
            'response_code' => 302,
        ]);
    }

    /** @test */
    function short_url_clicks_for_ignored_urls_are_not_logged()
    {
        $domain = create(Domain::class, ['url' => config('app.url')]);
        $loggedUrl = create(Url::class, ['domain_id' => $domain->id]);
        $ignoredUrl = factory(Url::class)->states('analytics_disabled')
            ->create(['domain_id' => $domain->id]);

        $this->get(route('short-url', $loggedUrl->url));
        $this->get(route('short-url', $ignoredUrl->url));
        $this->assertDatabaseMissing((new UrlAnalytics())->getTable(), [
            'request_uri' => $ignoredUrl->url,
        ]);
    }
}
