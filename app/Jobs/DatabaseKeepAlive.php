<?php

namespace App\Jobs;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DatabaseKeepAlive implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(EntityManagerInterface $em, Cache $cache)
    {
        $conn = $em->getConnection();
        try {
            $conn->executeQuery('SELECT 1')->free();
        } catch (\Exception $e) {
            if ($conn === null || stripos($e->getMessage(), 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away') === false) {
                throw $e;
            }
            $conn->close();
            $conn->connect();
        }

        $cache->forever('queue.job.last-keep-alive', Carbon::now());
    }
}
