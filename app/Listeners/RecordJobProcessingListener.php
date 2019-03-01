<?php

namespace App\Listeners;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Carbon;

class RecordJobProcessingListener
{
    /**
     * The cache service.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * Create the event listener.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @return void
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle the event.
     *
     * @param \Illuminate\Queue\Events\JobProcessing $event
     * @return void
     */
    public function handle(JobProcessing $event)
    {
        $this->cache->forever('queue.job.last-processed', Carbon::now());
    }
}
