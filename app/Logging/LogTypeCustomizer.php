<?php

namespace App\Logging;

class LogTypeCustomizer
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(function ($record) {
                $record['log_type'] = 'laravel_app';

                return $record;
            });
        }
    }
}
