<?php

namespace App\Listeners;

use App\Events\UrlEvent;
use App\Repositories\UrlRepository;
use Illuminate\Contracts\Queue\ShouldQueue;

class CacheShortUrlFromQueue extends CacheShortUrl implements ShouldQueue
{
    public function handle(UrlEvent $event)
    {
        $event->url = app(UrlRepository::class)->find($event->urlId);
        parent::handle($event);
    }
}
