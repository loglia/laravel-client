<?php

namespace Loglia\LaravelClient\Monolog\Formatting;

class RemoveLevelName implements Formatter
{
    /**
     * @inheritdoc
     */
    public function __invoke(array $record)
    {
        unset($record['level_name']);

        return $record;
    }
}
