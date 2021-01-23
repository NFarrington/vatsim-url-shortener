<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Traits\RecordsDataChanges;
use App\Exceptions\UnexpectedCallException;
use Illuminate\Contracts\Routing\UrlRoutable;

abstract class Entity implements UrlRoutable
{
    use RecordsDataChanges;

    protected array $trackedProperties = [];

    public function getRouteKey()
    {
        return $this->{$this->getRouteKeyName()};
    }

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        throw new UnexpectedCallException();
    }

    public function resolveChildRouteBinding($childType, $value, $field)
    {
        throw new UnexpectedCallException();
    }
}
