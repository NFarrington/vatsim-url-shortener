<?php

namespace Tests\Unit\Services;

use App\Exceptions\CacheFallbackException;
use App\Models\Url;
use App\Services\UrlService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PDOException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * @covers \App\Services\UrlService
 */
class UrlServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function falls_back_to_local_cache()
    {
        app()->bind(Url::class, function () {
            return new class extends Url {
                public function get($columns = ['*'])
                {
                    throw new QueryException('', [], null);
                }
            };
        });

        $domain = 'http://test-domain/';
        $url = 'my-url';
        $prefix = null;
        cache()->set(sprintf(Url::URL_CACHE_KEY, $domain, $prefix, $url), new Url(['my_url' => $url]));
        $service = new UrlService();

        $shortUrl = $service->getRedirectForUrl($domain, $url);

        $this->assertEquals($url, $shortUrl->my_url);
    }

    /** @test */
    function rethrows_pdo_exception_if_missing_from_cache()
    {
        app()->bind(Url::class, function () {
            return new class extends Url {
                public function get($columns = ['*'])
                {
                    throw new QueryException('', [], null);
                }
            };
        });
        $domain = 'http://test-domain/';
        $url = 'my-url';
        $service = new UrlService();

        try {
            $service->getRedirectForUrl($domain, $url);
        } catch (\Exception $e) {
            $this->assertInstanceOf(CacheFallbackException::class, $e);
            $this->assertInstanceOf(PDOException::class, $e->getPrevious());
            return;
        }

        $this->fail('Method getRedirectForUrl() did not throw an exception.');
    }

    /** @test */
    function prefixed_requests_dont_match_non_prefixed_organization_urls()
    {
        $url = factory(Url::class)->states('org')->create(['prefix' => false]);
        $service = new UrlService();

        $this->expectException(NotFoundHttpException::class);
        $service->getRedirectForUrl($url->domain->url, $url->url, $url->organization->prefix);
    }

    /** @test */
    function resolves_blank_paths_to_index_url()
    {
        $url = factory(Url::class)->states('org')->create(['url' => '/']);
        $service = new UrlService();

        $shortUrl = $service->getRedirectForUrl($url->domain->url, null, null);

        $this->assertEquals($url->id, $shortUrl->id);
    }
}
