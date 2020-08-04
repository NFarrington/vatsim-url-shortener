<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use RuntimeException;

class MigrateWithLock extends MigrateCommand
{
    protected $signature = 'migrate-with-lock {--database= : The database connection to use}
                {--force : Force the operation to run when in production}
                {--path=* : The path(s) to the migrations files to be executed}
                {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
                {--pretend : Dump the SQL queries that would be run}
                {--seed : Indicates if the seed task should be re-run}
                {--step : Force the migrations to be run so they can be rolled back individually}';

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
