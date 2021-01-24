<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Migrations\Migrator;
use RuntimeException;

class MigrateWithLock extends MigrateCommand
{
    /**
     * Create a new migration command instance.
     *
     * @param \Illuminate\Database\Migrations\Migrator $migrator
     * @param \Illuminate\Contracts\Events\Dispatcher $dispatcher
     * @return void
     */
    public function __construct(Migrator $migrator, Dispatcher $dispatcher)
    {
        $this->signature = preg_replace('/^migrate/', 'migrate-with-lock', $this->signature);

        parent::__construct($migrator, $dispatcher);
    }

    public function handle()
    {
        $results = DB::select('SELECT GET_LOCK("artisan-migrate", 60) as migrate');
        if (!$results[0]->migrate) {
            throw new RuntimeException('Timed out waiting for database lock.');
        }

        try {
            parent::handle();
        } finally {
            DB::select('SELECT RELEASE_LOCK("artisan-migrate")');
        }
    }
}
