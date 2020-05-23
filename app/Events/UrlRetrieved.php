<?php

namespace App\Events;

use App\Models\Url;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UrlRetrieved
{
    use Dispatchable, SerializesModels;

    /**
     * The Url model.
     *
     * @var \App\Models\Url
     */
    public $url;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Url $url
     * @return void
     */
    public function __construct(Url $url)
    {
        $this->url = $url;
    }
}
