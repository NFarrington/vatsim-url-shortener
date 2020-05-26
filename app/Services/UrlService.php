<?php

namespace App\Services;

use App\Exceptions\CacheFallbackException;
use App\Models\Url;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PDOException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlService
{
    public function getRedirectForUrl(string $domain, string $url = null, string $prefix = null): Url
    {
        $urlQuery = app(Url::class)->query()
            ->where('url', $url ?: '/')
            ->whereHas('domain', function ($query) use ($domain) {
                $query->where('url', $domain);
            });

        if ($prefix) {
            $urlQuery->where('prefix', true)
                ->whereHas('organization', function ($query) use ($prefix) {
                    $query->where('prefix', $prefix);
                });
        }

        $urlModel = null;
        try {
            $urlModel = $urlQuery->first();
        } catch (PDOException $e) {
            $urlModel = $this->fallbackToCache($domain, $url, $prefix);
            if (!$urlModel) {
                throw new CacheFallbackException('Could not find URL in fallback cache.', 0, $e);
            } else {
                report($e);
            }
        }

        if (!$urlModel) {
            throw new NotFoundHttpException();
        }

        return $urlModel;
    }

    /**
     * @param string $domain
     * @param string $url
     * @param string|null $prefix
     * @return Url|null
     */
    private function fallbackToCache(string $domain, string $url, string $prefix = null)
    {
        Log::warning('Failed to retrieve URL from database, attempting to retrieve from cache.',
            ['domain' => $domain, 'prefix' => $prefix, 'url' => $url]);

        $cachedModel = Cache::get(sprintf(Url::URL_CACHE_KEY, $domain, $prefix, $url));

        if ($cachedModel) {
            Log::info('Successfully retrieved cached version of URL.',
                ['domain' => $domain, 'prefix' => $prefix, 'url' => $url, 'last_updated' => $cachedModel->updated_at]);
        } else {
            Log::error('Failed to retrieve cached version of URL.',
                ['domain' => $domain, 'prefix' => $prefix, 'url' => $url]);
        }

        return $cachedModel;
    }
}
