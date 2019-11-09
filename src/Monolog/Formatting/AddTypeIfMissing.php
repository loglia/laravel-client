<?php

namespace Loglia\LaravelClient\Monolog\Formatting;

class AddTypeIfMissing implements Formatter
{
    /**
     * @inheritdoc
     */
    public function __invoke(array $record)
    {
        if (empty($record['extra']['--loglia']['type'])) {
            $record['extra']['--loglia']['type'] = 'log';
        }

        return $record;
    }
}
