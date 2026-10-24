<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ReadsListingOrder
{
    /**
     * Read the `sort` and `direction` query parameters for a listing, falling back to the defaults for any value
     * that isn't allowed. The returned values are always from the allow-lists, so they're safe to put into DQL.
     * The default direction applies to the default column; other columns default to ascending.
     *
     * @param  string[]  $columns  the columns the listing can be sorted by
     * @return array{0: string, 1: string} the column and direction
     */
    protected function listingOrder(Request $request, array $columns, string $defaultColumn, string $defaultDirection = 'asc'): array
    {
        $column = $request->query('sort');
        $direction = $request->query('direction');

        $column = in_array($column, $columns, true) ? $column : $defaultColumn;
        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = $column === $defaultColumn ? $defaultDirection : 'asc';
        }

        return [$column, $direction];
    }
}
