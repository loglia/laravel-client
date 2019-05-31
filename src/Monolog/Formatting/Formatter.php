<?php

namespace Retrospekt\LaravelClient\Monolog\Formatting;

interface Formatter
{
    /**
     * Takes a record array, formats it somehow, and passes it back.
     *
     * @param array $record
     * @return array
     */
    public function __invoke(array $record);
}
