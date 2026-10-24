<?php

namespace Tests;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\MockInterface;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $app->instance(
            DynamoDbClient::class,
            Mockery::mock(DynamoDbClient::class, function (MockInterface $mock) {
                $mock->allows('putItem');
                $mock->allows('deleteItem');
            })
        );

        return $app;
    }
}
