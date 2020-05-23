<?php

namespace Tests\Unit\Services;

use App\Models\Url;
use App\Services\UrlService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
        cache()->set(sprintf(UrlService::URL_CACHE_KEY, $domain, $prefix, $url), new Url(['my_url' => $url]));
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
            $this->assertInstanceOf(\PDOException::class, $e->getPrevious());
            return;
        }

        $this->fail('Method getRedirectForUrl() did not throw an exception.');
    }
}
