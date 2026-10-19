<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

class EcsFormatter extends JsonFormatter
{
    protected function normalizeRecord(LogRecord $record): array
    {
        $normalized = parent::normalizeRecord($record);

        if (empty($normalized['datetime'])) {
            $normalized['datetime'] = gmdate('c');
        }
        $normalized['@timestamp'] = $normalized['datetime'];
        unset($normalized['datetime']);

        if (!empty($normalized['level_name'])) {
            if (!isset($normalized['log'])) {
                $normalized['log'] = [];
            }
            $normalized['log']['level'] = $normalized['level_name'];
            unset($normalized['level']);
            unset($normalized['level_name']);
        }

        if (!empty($normalized['channel'])) {
            if (!isset($normalized['log'])) {
                $normalized['log'] = [];
            }
            $normalized['log']['logger'] = $normalized['channel'];
            unset($normalized['channel']);
        }

        if (!isset($normalized['event'])) {
            $normalized['event'] = [];
        }
        $normalized['event']['dataset'] = 'laravel.application';

        return $normalized;
    }
}
