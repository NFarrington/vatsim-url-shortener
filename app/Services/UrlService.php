<?php

namespace App\Services;

use App\Models\Organization;
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
            $organization = Organization::where('prefix', $prefix)->first();
            if (!$organization) {
                throw new NotFoundHttpException();
            }
            $urlQuery = $urlQuery->where('organization_id', $organization->id);
        }

        /** @var Url $url */
        $url = $urlQuery->first();

        if (!$url) {
            throw new NotFoundHttpException();
        }

        return $url;
    }
}
