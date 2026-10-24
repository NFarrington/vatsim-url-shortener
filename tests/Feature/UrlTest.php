<?php

namespace Tests\Feature;

use App\Entities\Domain;
use App\Entities\Url;
use App\Entities\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Tests\Traits\RefreshDatabase;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    function short_url_without_prefix_redirects_to_its_intended_target()
    {
        $domain = create(Domain::class, ['url' => config('app.url').'/']);
        $url = create(Url::class, ['domain' => $domain]);
        EntityManager::clear();

        $this->get(route('short-url', $url->getUrl()))
            ->assertRedirect($url->getRedirectUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    function short_url_with_unknown_prefix_returns_404()
    {
        $this->withExceptionHandling();

        $domain = create(Domain::class, ['url' => config('app.url').'/']);
        $url = entity(Url::class)->states('org')
            ->create(['domain' => $domain, 'prefix' => false]);

        $this->get($url->getFullUrl().'/'.Str::random())
            ->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function short_url_returns_404_when_it_doesnt_exist()
    {
        $this->withExceptionHandling();

        $this->get(route('short-url', Str::random()))->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function short_url_returns_404_when_its_domain_doesnt_match()
    {
        $this->withExceptionHandling();

        $url = create(Url::class);
        $this->get(route('short-url', $url->getUrl()))->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function anonymous_short_url_redirect_does_not_start_a_session()
    {
        $domain = create(Domain::class, ['url' => config('app.url').'/']);
        $url = create(Url::class, ['domain' => $domain]);

        $this->get(route('short-url', $url->getUrl()))
            ->assertRedirect($url->getRedirectUrl())
            ->assertCookieMissing(config('session.cookie'));
        $this->assertFalse($this->app['session']->driver()->isStarted());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function anonymous_short_url_404_does_not_start_a_session()
    {
        $this->withExceptionHandling();

        $this->get(route('short-url', Str::random()))
            ->assertNotFound()
            ->assertCookieMissing(config('session.cookie'));
        $this->assertFalse($this->app['session']->driver()->isStarted());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function short_url_redirects_for_a_logged_in_user()
    {
        $domain = create(Domain::class, ['url' => config('app.url').'/']);
        $url = create(Url::class, ['domain' => $domain]);
        $user = create(User::class);

        // Store a logged-in session, as the platform would, and send its cookie.
        $session = $this->app['session']->driver();
        $session->put(Auth::guard('web')->getName(), $user->getAuthIdentifier());
        $session->save();
        $session->flush(); // so the request can only find the user in the stored session

        $this->withCookie(config('session.cookie'), $session->getId())
            ->get(route('short-url', $url->getUrl()))
            ->assertRedirect($url->getRedirectUrl());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function url_analytics_table_is_dropped()
    {
        $this->assertFalse(Schema::hasTable('url_analytics'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function short_url_for_ip_address_resolves_to_app_url()
    {
        $domain = create(Domain::class, ['url' => config('app.url').'/']);
        $url = create(Url::class, ['domain' => $domain]);
        EntityManager::clear();

        $this->get('http://127.0.0.1/'.$url->getUrl())
            ->assertRedirect($url->getRedirectUrl());
    }
}
