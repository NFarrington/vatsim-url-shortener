<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Queue\Queue;

class QueueHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the health of the queue.';

    /**
     * The cache service.
     *
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;

    /**
     * The queue service.
     *
     * @var \Illuminate\Contracts\Queue\Queue
     */
    protected $queue;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Contracts\Cache\Repository $cache
     * @param \Illuminate\Contracts\Queue\Queue $queue
     * @return void
     */
    public function __construct(Cache $cache, Queue $queue)
    {
        parent::__construct();

        $this->cache = $cache;
        $this->queue = $queue;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $queueSize = $this->queue->size();
        if ($queueSize == 0) {
            $this->line('The queue is empty.');

            return 0;
        }

        /** @var \Illuminate\Support\Carbon $lastProcessed */
        $lastProcessed = $this->cache->get('queue.job.last-processed');
        if (!$lastProcessed || $lastProcessed->diffInSeconds() > 30) {
            $this->line("The queue has $queueSize items, and appears to be stale.");

            return 1;
        }

        $this->line("The queue has $queueSize items, and is processing.");

        return 0;
    }
}
