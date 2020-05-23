<?php

namespace App\Services;

use App\Exceptions\ReportedException;
use App\Models\Url;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use PDOException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlService
{
    public function getRedirectForUrl(string $domain, string $url, string $prefix = null): Url
    {
        /** @var Url|Builder $urlQuery */
        $urlQuery = app(Url::class)->query();

        $urlQuery->where('url', $url ?: '/')
            ->whereHas('domain', function ($query) use ($domain) {
                $query->where('url', $domain);
            });

        if ($prefix) {
            $urlQuery->whereHas('organization', function ($query) use ($prefix) {
                $query->where('prefix', $prefix);
            });
        }

        /** @var Url|null $urlModel */
        $urlModel = null;
        try {
            $urlModel = $urlQuery->first();
        } catch (PDOException $e) {
            report($e);
            Log::warning('Failed to retrieve URL from database, attempting to retrieve from cache.',
                ['domain' => $domain, 'prefix' => $prefix, 'url' => $url]);
            $urlModel = $this->loadUrlFromCache($domain, $url, $prefix);
            if ($urlModel) {
                Log::info('Successfully retrieved cached version of URL.',
                    ['domain' => $domain, 'prefix' => $prefix, 'url' => $url, 'last_updated' => $urlModel->updated_at]);
            } else {
                Log::error('Failed to retrieve cached version of URL.',
                    ['domain' => $domain, 'prefix' => $prefix, 'url' => $url]);
                throw new ReportedException('', 0, $e);
            }
        }

        if (!$urlModel) {
            throw new NotFoundHttpException();
        }

        return $urlModel;
    }

    private function loadUrlFromCache(string $domain, string $url, string $prefix = null)
    {
        return Cache::get(sprintf(Url::URL_CACHE_KEY, $domain, $prefix, $url));
    }
}
