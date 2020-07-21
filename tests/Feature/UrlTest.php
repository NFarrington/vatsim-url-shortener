<?php

namespace Tests\Feature;

use App\Entities\Domain;
use App\Entities\Url;
use App\Entities\UrlAnalytics;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function short_url_without_prefix_redirects_to_its_intended_target()
    {
        $domain = create(Domain::class, ['url' => config('app.url').'/']);
        $url = create(Url::class, ['domain' => $domain]);
        EntityManager::clear();

        $this->get(route('short-url', $url->getUrl()))
            ->assertRedirect($url->getRedirectUrl());
    }

    /** @test */
    function short_url_with_prefix_redirects_to_its_intended_target()
    {
        $domain = create(Domain::class, ['url' => config('app.url').'/']);
        $url = entity(Url::class)->states('org')
            ->create(['domain' => $domain, 'prefix' => true]);
        $url->getOrganization()->setPrefix(Str::random(3));
        EntityManager::flush();
        EntityManager::clear();

        $this->get($url->getFullUrl())
            ->assertRedirect($url->getRedirectUrl());
    }

    /** @test */
    function short_url_with_unknown_prefix_returns_404()
    {
        $this->expectException(NotFoundHttpException::class);

        $domain = create(Domain::class, ['url' => config('app.url').'/']);
        $url = entity(Url::class)->states('org')
            ->create(['domain' => $domain, 'prefix' => false]);

        $this->get($url->getFullUrl().'/'.Str::random())
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
        $this->get(route('short-url', $url->getUrl()))->assertNotFound();
    }

    /** @test */
    function short_url_clicks_are_logged()
    {
        $domain = create(Domain::class, ['url' => config('app.url').'/']);
        $url = create(Url::class, ['domain' => $domain]);

        $this->get(route('short-url', $url->getUrl()));
        $this->assertDatabaseHas(EntityManager::getClassMetadata(UrlAnalytics::class)->getTableName(), [
            'request_uri' => $url->getUrl(),
            'response_code' => 302,
        ]);
    }

    /** @test */
    function short_url_clicks_for_ignored_urls_are_not_logged()
    {
        $domain = create(Domain::class, ['url' => config('app.url').'/']);
        $loggedUrl = create(Url::class, ['domain' => $domain]);
        $ignoredUrl = entity(Url::class)->states('analytics_disabled')
            ->create(['domain' => $domain]);

        $this->get(route('short-url', $loggedUrl->getUrl()));
        $this->get(route('short-url', $ignoredUrl->getUrl()));
        $this->assertDatabaseMissing(EntityManager::getClassMetadata(UrlAnalytics::class)->getTableName(), [
            'request_uri' => $ignoredUrl->getUrl(),
        ]);
    }
}
