<?php

namespace Tests\Unit\Logging;

use App\Logging\LogTypeCustomizer;
use App\Logging\StacktraceCustomizer;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Tests\TestCase;

class StacktraceCustomizerTest extends TestCase
{

    /** @test */
    public function enables_stacktraces()
    {
        $formatter = new JsonFormatter();
        $testHandler = new TestHandler();
        $testHandler->setFormatter($formatter);
        $logger = new Logger('test-logger', [$testHandler]);
        $customizer = new StacktraceCustomizer();

        $customizer($logger);
        $logger->info('This is a test!', ['exception' => new \Exception('Test exception.')]);

        $records = $testHandler->getRecords();
        $this->assertCount(1, $records);
        $record = json_decode($records[0]['formatted']);
        $this->assertObjectHasAttribute('trace', $record->context->exception);
    }

}
