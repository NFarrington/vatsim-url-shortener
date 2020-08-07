<?php

namespace Tests\Unit\Logging;

use App\Logging\EcsCustomizer;
use App\Logging\EcsFormatter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Tests\TestCase;

class EcsFormatterTest extends TestCase
{
    /** @test */
    public function adds_event_dataset_to_logs()
    {
        $testHandler = new TestHandler();
        $testHandler->setFormatter(new EcsFormatter());
        $logger = new Logger('test-logger', [$testHandler]);

        $logger->info('This is a test!');

        $records = $testHandler->getRecords();
        $this->assertCount(1, $records);
        $jsonRecord = json_decode($records[0]['formatted'], true);
        $this->assertEquals(
            'laravel.application',
            $jsonRecord['event']['dataset']
        );
    }
}
