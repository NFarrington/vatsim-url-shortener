<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;

class EcsFormatter extends JsonFormatter
{
    public function format(array $record): string
    {
        if (empty($record['datetime'])) {
            $record['datetime'] = gmdate('c');
        }
        $record['@timestamp'] = $record['datetime'];
        unset($record['datetime']);

        if (!empty($record['level_name'])) {
            if (!isset($record['log'])) {
                $record['log'] = [];
            }
            $record['log']['level'] = $record['level_name'];
            unset($record['level']);
            unset($record['level_name']);
        }

        if (!empty($record['channel'])) {
            if (!isset($record['log'])) {
                $record['log'] = [];
            }
            $record['log']['logger'] = $record['channel'];
            unset($record['channel']);
        }

        if (!isset($record['event'])) {
            $record['event'] = [];
        }
        $record['event']['dataset'] = 'laravel.application';

        return parent::format($record);
    }
}
