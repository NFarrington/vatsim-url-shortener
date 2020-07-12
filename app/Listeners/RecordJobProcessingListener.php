<?php

namespace App\Listeners;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Carbon;

class RecordJobProcessingListener
{
    protected $cache;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function handle(JobProcessing $event)
    {
        $this->cache->forever('queue.job.last-processed', Carbon::now());
    }
}
