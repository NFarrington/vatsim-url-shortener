<?php

/**
 * Obtain the breadcrumbs for the current URI.
 *
 * @return string
 */
function breadcrumbs()
{
    $path = Request::decodedPath();
    if ($path === '/') {
        return 'Dashboard';
    }

    return title_case(str_replace('/', ' / ', $path));
}
