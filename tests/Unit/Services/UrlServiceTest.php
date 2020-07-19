<?php

namespace Tests\Unit\Services;

use App\Entities\Url;
use App\Exceptions\CacheFallbackException;
use App\Services\UrlService;
use Carbon\Carbon;
use Doctrine\DBAL\DBALException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use LaravelDoctrine\ORM\EntityManagerFactory;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;
use Tests\Traits\RefreshDatabase;

/**
 * @covers \App\Services\UrlService
 */
class UrlServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function falls_back_to_local_cache()
    {
        $this->withInaccessibleDatabase();

        $domain = 'http://test-domain/';
        $url = new Url();
        $urlPath = 'my-url';
        $url->setUrl($urlPath);
        $url->setCreatedAt(Carbon::now());
        $url->setUpdatedAt(Carbon::now());
        $prefix = null;
        cache()->set(sprintf(Url::URL_CACHE_KEY, $domain, $prefix, $urlPath), $url);
        $service = $this->app->make(UrlService::class);

        $shortUrl = $service->getRedirectForUrl($domain, $urlPath);

        $this->assertEquals($urlPath, $shortUrl->getUrl());
    }

    /** @test */
    function reports_pdo_exception_if_missing_from_cache()
    {
        $this->withInaccessibleDatabase();
        $exceptionHandlerSpy = $this->trackReportedExceptions();

        $domain = 'http://test-domain/';
        $url = 'my-url';
        $service = $this->app->make(UrlService::class);

        $exceptionHandlerSpy->shouldHaveReceived('report', [DBALException::class]);

        try {
            $service->getRedirectForUrl($domain, $url);
            $this->fail('Method getRedirectForUrl() did not throw an exception.');
        } catch (\Exception $e) {
            $this->assertInstanceOf(CacheFallbackException::class, $e);
        }
    }

    /** @test */
    function prefixed_requests_dont_match_non_prefixed_organization_urls()
    {
        $url = entity(Url::class)->states('org')->create(['prefix' => false]);
        $service = $this->app->make(UrlService::class);

        $this->expectException(NotFoundHttpException::class);
        $service->getRedirectForUrl($url->getDomain()->getUrl(), $url->getUrl(), $url->getOrganization()->getPrefix());
    }

    /** @test */
    function resolves_blank_paths_to_index_url()
    {
        $url = entity(Url::class)->states('org')->create(['url' => '/']);
        $service = $this->app->make(UrlService::class);

        $shortUrl = $service->getRedirectForUrl($url->getDomain()->getUrl(), null, null);

        $this->assertEquals($url->getId(), $shortUrl->getId());
    }

    private function withInaccessibleDatabase()
    {
        $this->app['config']->set('database.connections.invalid', ['driver' => 'mysql', 'host' => '0.0.0.0']);
        $factory = $this->app[EntityManagerFactory::class];
        $entityManager = $factory->create(
            ['connection' => 'invalid', 'proxies' => ['path' => storage_path('proxies')]]
        );
        $this->app->singleton('em', fn($app) => $entityManager);
    }

    /**
     * @return MockInterface
     */
    private function trackReportedExceptions()
    {
        $this->app->extend(
            ExceptionHandler::class,
            function ($handler) use (&$mock) {
                $mock = Mockery::mock($handler);
                return $mock;
            }
        );

        return $mock;
    }
}
