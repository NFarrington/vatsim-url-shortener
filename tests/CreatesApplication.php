<?php

namespace Tests;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

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

        return $app;
    }
}
