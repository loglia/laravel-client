<?php

namespace Loglia\LaravelClient\Monolog\Formatting;

class AddTypeIfMissing implements Formatter
{
    /**
     * @inheritdoc
     */
    public function __invoke(array $record)
    {
        if (empty($record['extra']['__loglia']['type'])) {
            $record['extra']['__loglia']['type'] = 'log';
        }

        return $record;
    }
}
