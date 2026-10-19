<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class QueueHealthTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    function queue_is_healthy_when_empty()
    {
        $mock = $this->createStub(\Illuminate\Contracts\Queue\Queue::class);
        $mock->method('size')->willReturn(0);
        $this->app->instance(\Illuminate\Contracts\Queue\Queue::class, $mock);

        $this->artisan('queue:health')->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function queue_is_healthy_when_a_job_was_recently_processed()
    {
        Cache::forever('queue.job.last-processed', \Carbon\Carbon::now());

        $mock = $this->createStub(\Illuminate\Contracts\Queue\Queue::class);
        $mock->method('size')->willReturn(1);
        $this->app->instance(\Illuminate\Contracts\Queue\Queue::class, $mock);

        $this->artisan('queue:health')->assertExitCode(0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    function queue_is_unhealthy_when_a_job_was_not_recently_processed()
    {
        Cache::forever('queue.job.last-processed', \Carbon\Carbon::now()->subHour());

        $mock = $this->createStub(\Illuminate\Contracts\Queue\Queue::class);
        $mock->method('size')->willReturn(1);
        $this->app->instance(\Illuminate\Contracts\Queue\Queue::class, $mock);

        $this->artisan('queue:health')->assertExitCode(1);
    }
}
