<?php

namespace App\Events;

use App\Entities\Url;
use Illuminate\Foundation\Events\Dispatchable;

abstract class UrlEvent
{
    use Dispatchable;

    public ?Url $url;
    public int $urlId;

    public function __construct(Url $url)
    {
        $this->url = $url;
        $this->urlId = $url->getId();
    }
}
