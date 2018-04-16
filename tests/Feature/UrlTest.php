<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Url;
use App\Models\UrlAnalytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function short_url_redirects_to_its_intended_target()
    {
        $domain = create(Domain::class, ['url' => config('app.url')]);
        $url = create(Url::class, ['domain_id' => $domain->id]);

        $this->get(route('short-url', $url->url))
            ->assertRedirect($url->redirect_url);
    }

    /** @test */
    function short_url_returns_404_when_it_doesnt_exist()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->get(route('short-url', str_random()))->assertNotFound();
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
}
