<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\FormattableHandlerInterface;

class StacktraceCustomizer
{
    /**
     * Customize the given logger instance.
     *
     * @param \Illuminate\Log\Logger $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $this->enableStacktraces($handler);
        }
    }

    private function enableStacktraces($handler)
    {
        $formatter = $handler instanceof FormattableHandlerInterface
            ? $handler->getFormatter()
            : null;

        if ($formatter instanceof JsonFormatter) {
            $formatter->includeStacktraces(true);
        }
    }
}
