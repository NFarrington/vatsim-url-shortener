<?php

namespace Tests\Traits;

use Composer\Autoload\ClassMapGenerator;
use DB;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Schema;

trait RefreshDatabase
{
    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function refreshDatabase()
    {
        $this->usingInMemoryDatabase()
            ? $this->refreshInMemoryDatabase()
            : $this->refreshTestDatabase();
    }

    /**
     * Determine if an in-memory database is being used.
     *
     * @return bool
     */
    protected function usingInMemoryDatabase()
    {
        $default = config('database.default');

        return config("database.connections.$default.database") === ':memory:';
    }

    /**
     * Refresh the in-memory database.
     *
     * @return void
     */
    protected function refreshInMemoryDatabase()
    {
        $this->artisan('migrate');

        $this->app[Kernel::class]->setArtisan(null);
    }

    /**
     * Refresh a conventional test database.
     *
     * @return void
     */
    protected function refreshTestDatabase()
    {
        if (! RefreshDatabaseState::$migrated) {
            $this->artisan('migrate:fresh', [
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
            ]);

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        }

        //$this->beginEloquentDatabaseTransaction();
        $this->beginDoctrineDatabaseTransaction();
    }

    /**
     * Begin a database transaction on the testing database.
     *
     * @return void
     */
    public function beginEloquentDatabaseTransaction()
    {
        $database = $this->app->make('db');

        foreach ($this->connectionsToTransact() as $name) {
            $connection = $database->connection($name);
            $dispatcher = $connection->getEventDispatcher();

            $connection->unsetEventDispatcher();
            $connection->beginTransaction();
            $connection->setEventDispatcher($dispatcher);
        }

        $this->beforeApplicationDestroyed(function () use ($database) {
            foreach ($this->connectionsToTransact() as $name) {
                $connection = $database->connection($name);
                $dispatcher = $connection->getEventDispatcher();

                $connection->unsetEventDispatcher();
                $connection->rollback();
                $connection->setEventDispatcher($dispatcher);
                $connection->disconnect();
            }
        });
    }

    /**
     * Begin a database transaction on the testing database.
     *
     * @return void
     */
    public function beginDoctrineDatabaseTransaction()
    {
        // this is preferred, but doesn't work with $this->assertDatabaseHas
        $connection = $this->app->make('em')->getConnection();
        $connection->beginTransaction();

        $this->beforeApplicationDestroyed(function () use ($connection) {
            $connection->rollBack();
        });

        //$dir = base_path().'/app/Entities';
        //$dirs = glob($dir, GLOB_ONLYDIR);
        //Schema::disableForeignKeyConstraints();
        ////DB::statement('SET FOREIGN_KEY_CHECKS=0');
        //foreach ($dirs as $dir) {
        //    if (file_exists($dir)) {
        //        $classMap = ClassMapGenerator::createMap($dir);
        //
        //        // Sort list so it's stable across different environments
        //        ksort($classMap);
        //
        //        foreach ($classMap as $entity => $path) {
        //            $reflection = new \ReflectionClass($entity);
        //            if (!$reflection->isInstantiable()) {
        //                continue;
        //            }
        //            $table = EntityManager::getClassMetadata($entity)
        //                ->getTableName();
        //            DB::table($table)->truncate();
        //        }
        //    }
        //}
        //Schema::enableForeignKeyConstraints();
        ////DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * The database connections that should have transactions.
     *
     * @return array
     */
    protected function connectionsToTransact()
    {
        return property_exists($this, 'connectionsToTransact')
            ? $this->connectionsToTransact : [null];
    }

    /**
     * Determine if views should be dropped when refreshing the database.
     *
     * @return bool
     */
    protected function shouldDropViews()
    {
        return property_exists($this, 'dropViews')
            ? $this->dropViews : false;
    }

    /**
     * Determine if types should be dropped when refreshing the database.
     *
     * @return bool
     */
    protected function shouldDropTypes()
    {
        return property_exists($this, 'dropTypes')
            ? $this->dropTypes : false;
    }
}
