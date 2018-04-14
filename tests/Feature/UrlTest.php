<?php

namespace Tests\Feature;

use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class UrlTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function short_url_redirects_to_its_intended_target()
    {
        /* @var \App\Models\Url $url */
        $url = create(Url::class);

        $this->get(route('short-url', $url->url))
            ->assertRedirect($url->redirect_url);
    }

    /** @test */
    public function short_url_returns_404_when_it_doesnt_exist()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->get(route('short-url', str_random()));
    }
}
