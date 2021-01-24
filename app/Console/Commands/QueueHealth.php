<?php

namespace App\Console\Commands;

use App\Jobs\DatabaseKeepAlive;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Queue\Queue;
use Illuminate\Support\Carbon;

class QueueHealth extends Command
{
    protected $signature = 'queue:health';
    protected $description = 'Get the health of the queue.';

    protected Cache $cache;
    protected Queue $queue;

    public function __construct(Cache $cache, Queue $queue)
    {
        parent::__construct();

        $this->cache = $cache;
        $this->queue = $queue;
    }

    public function handle()
    {
        $queueHealth = $this->getQueueHealth();

        $lastKeepAlive = $this->cache->get('queue.job.last-keep-alive');
        if (!$lastKeepAlive || $lastKeepAlive->diffInMinutes() >= 5) {
            DatabaseKeepAlive::dispatch();
        }

        return $queueHealth;
    }

    private function getQueueHealth()
    {
        $queueSize = $this->queue->size();
        if ($queueSize == 0) {
            $this->line('The queue is empty.');

            return 0;
        }

        /** @var Carbon $lastProcessed */
        $lastProcessed = $this->cache->get('queue.job.last-processed');
        if (!$lastProcessed || $lastProcessed->diffInSeconds() >= 30) {
            $this->line("The queue has $queueSize items, and appears to be stale.");

            return 1;
        }

        $this->line("The queue has $queueSize items, and is processing.");

        return 0;
    }
}
