<?php

namespace App\Services;

use App\Entities\Url;
use App\Exceptions\CacheFallbackException;
use App\Repositories\UrlRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlService
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getRedirectForUrl(string $domain, string $url = null, string $prefix = null): Url
    {
        $urlEntity = null;
        try {
            $urlEntity = app(UrlRepository::class)->findByDomainAndUrlAndPrefix($domain, $url ?: '/', $prefix);
        } catch (DBALException $e) {
            $urlEntity = $this->fallbackToCache($domain, $url, $prefix);
            if (!$urlEntity) {
                throw new CacheFallbackException('Could not find URL in fallback cache.', 0, $e);
            } else {
                report($e);
            }
        }

        if (!$urlEntity) {
            throw new NotFoundHttpException();
        }

        return $urlEntity;
    }

    /**
     * @param string $domain
     * @param string $url
     * @param string|null $prefix
     * @return Url|null
     */
    private function fallbackToCache(string $domain, string $url = null, string $prefix = null)
    {
        Log::warning('Failed to retrieve URL from database, attempting to retrieve from cache.',
            ['domain' => $domain, 'prefix' => $prefix, 'url' => $url]);

        /** @var Url $cachedModel */
        $cachedModel = Cache::get(sprintf(Url::URL_CACHE_KEY, $domain, $prefix, $url));

        if ($cachedModel) {
            Log::info('Successfully retrieved cached version of URL.',
                ['domain' => $domain, 'prefix' => $prefix, 'url' => $url, 'last_updated' => $cachedModel->getUpdatedAt()]);
        } else {
            Log::error('Failed to retrieve cached version of URL.',
                ['domain' => $domain, 'prefix' => $prefix, 'url' => $url]);
        }

        return $cachedModel;
    }
}
