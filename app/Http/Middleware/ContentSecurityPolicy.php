<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Add a Content Security Policy with a per-request script nonce.
     *
     * The nonce is shared with views as $cspNonce. Referrer-Policy and
     * Permissions-Policy are set by nginx (docker/server.conf).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle($request, Closure $next)
    {
        $nonce = base64_encode(random_bytes(16));
        View::share('cspNonce', $nonce);

        /** @var Response $response */
        $response = $next($request);

        $header = config('csp.enforce')
            ? 'Content-Security-Policy'
            : 'Content-Security-Policy-Report-Only';
        $response->headers->set($header, $this->policy($nonce));

        return $response;
    }

    protected function policy(string $nonce): string
    {
        // 'strict-dynamic' lets the nonced Google Tag Manager loader bring in
        // its own scripts; the hosts after it are for browsers without support.
        $directives = [
            'default-src' => ["'self'"],
            'script-src' => ["'nonce-$nonce'", "'strict-dynamic'", 'https://www.googletagmanager.com'],
            'style-src' => ["'self'", 'https://fonts.googleapis.com'],
            'font-src' => ["'self'", 'https://fonts.gstatic.com'],
            'img-src' => ["'self'", 'data:', 'https://www.googletagmanager.com', 'https://*.google-analytics.com'],
            'connect-src' => [
                "'self'",
                'https://notify.bugsnag.com',
                'https://sessions.bugsnag.com',
                'https://*.google-analytics.com',
                'https://*.analytics.google.com',
                'https://*.googletagmanager.com',
            ],
            'frame-src' => ['https://www.googletagmanager.com'],
            'object-src' => ["'none'"],
            'base-uri' => ["'self'"],
            'frame-ancestors' => ["'self'"],
            'form-action' => ["'self'"],
        ];

        return implode('; ', array_map(
            fn ($name, $sources) => $name.' '.implode(' ', $sources),
            array_keys($directives),
            $directives
        ));
    }
}
