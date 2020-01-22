<?php

namespace Loglia\LaravelClient\Monolog;

class LogliaMonologV1Handler extends LogliaHandler
{
    public function write(array $record)
    {
        parent::write($record);
    }
}
