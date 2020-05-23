<?php

namespace Tests\Unit\Logging;

use App\Logging\LogTypeCustomizer;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Tests\TestCase;

class LogTypeCustomizerTest extends TestCase
{

    /** @test */
    public function adds_a_log_type_to_logs()
    {
        $testHandler = new TestHandler();
        $logger = new Logger('test-logger', [$testHandler]);
        $customizer = new LogTypeCustomizer();

        $customizer($logger);
        $logger->info('This is a test!');

        $records = $testHandler->getRecords();
        $this->assertCount(1, $records);
        $this->assertEquals(
            'laravel_app',
            $records[0]['log_type']
        );
    }

}
