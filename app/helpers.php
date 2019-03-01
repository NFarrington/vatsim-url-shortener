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
 * Filter an array by its keys using a callback.
 *
 * @param array $array
 * @param $callback
 * @return array
 */
function array_filter_key(array $array, $callback)
{
    $matchedKeys = array_filter(array_keys($array), $callback);

    return array_intersect_key($array, array_flip($matchedKeys));
}

/**
 * Obtain the breadcrumbs for the current URI.
 *
 * @return string
 */
function breadcrumbs()
{
    $path = Request::decodedPath();
    if ($path === '/') {
        return 'Home';
    }

    return title_case(str_replace(['/', '-'], [' / ', ' '], $path));
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
            'name' => title_case(str_replace('-', ' ', $segment)),
            'path' => $uri,
        ];
    }

    return $segments;
}

/**
 * Retrieves a Docker secret.
 *
 * @param string $name
 * @return string|null
 */
function docker_secret(string $name)
{
    $file = "/run/secrets/$name";

    if (empty($name) || !file_exists($file)) {
        return null;
    }

    return trim(file_get_contents($file));
}

/**
 * Replace hyphens with non-breaking hyphens.
 *
 * @param $text
 * @return mixed
 */
function hyphen_nobreak($text)
{
    return str_replace('-', html_entity_decode('&#8209;'), $text);
}
