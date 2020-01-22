<?php

namespace Loglia\LaravelClient\Monolog;

class LogliaMonologV2Handler extends LogliaHandler
{
    public function write(array $record): void
    {
        parent::write($record);
    }
}
