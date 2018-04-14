<?php

/**
 * Retrieve the application key from the configuration.
 *
 * @return string
 */
function app_key()
{
    $key = app()['config']['app.key'];
    if (starts_with($key, 'base64:')) {
        $key = base64_decode(mb_substr($key, 7));
    }

    return $key;
}

/**
 * Obtain the breadcrumbs for the current URI.
 *
 * @return string
 */
function breadcrumbs()
{
    $path = Request::decodedPath();

    return title_case(str_replace('/', ' / ', $path));
}

/**
 * Obtain the breadcrumbs for the current URI.
 *
 * @return array
 */
function breadcrumbs_array()
{
    $path = Request::decodedPath();

    $segments = [];
    $uri = '';
    foreach (explode('/', $path) as $segment) {
        $uri .= '/'.$segment;
        $segments[] = [
            'name' => title_case($segment),
            'path' => $uri,
        ];
    }

    return $segments;
}
