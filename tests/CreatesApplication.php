<?php

namespace Tests;

use Aws\SimpleDb\SimpleDbClient;
use Doctrine\Common\Annotations\AnnotationRegistry;
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

        // https://www.doctrine-project.org/projects/doctrine-annotations/en/1.10/index.html
        AnnotationRegistry::registerLoader('class_exists');

        $app->instance(
            SimpleDbClient::class,
            Mockery::mock(SimpleDbClient::class, function (MockInterface $mock) {
                $mock->allows('putAttributes');
                $mock->allows('deleteAttributes');
            })
        );

        return $app;
    }
}
