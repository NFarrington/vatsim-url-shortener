<?php

namespace App\Services;

use App\Models\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlService
{
    public function getRedirectForUrl(string $domain, string $url, string $prefix = null): Url
    {
        $urlQuery = Url::where('url', $url ?: '/')
            ->whereHas('domain', function ($query) use ($domain) {
                $query->where('url', $domain);
            });

        if ($prefix) {
            $urlQuery->whereHas('organization', function ($query) use ($prefix) {
                $query->where('prefix', $prefix);
            });
        }

        /** @var Url $url */
        $url = $urlQuery->first();

        if (!$url) {
            throw new NotFoundHttpException();
        }

        return $url;
    }
}
