<?php

namespace Loglia\LaravelClient\Monolog;

use Monolog\Handler\AbstractProcessingHandler;

class LogliaMonologV1Handler extends AbstractProcessingHandler
{
    use HandlesLogs;

    protected function write(array $record)
    {
        $this->sendToLoglia($record);
    }
}
