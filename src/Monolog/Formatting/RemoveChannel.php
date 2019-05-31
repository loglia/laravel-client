<?php

namespace Retrospekt\LaravelClient\Monolog\Formatting;

class RemoveChannel implements Formatter
{
    /**
     * @inheritdoc
     */
    public function __invoke(array $record)
    {
        unset($record['channel']);

        return $record;
    }
}
