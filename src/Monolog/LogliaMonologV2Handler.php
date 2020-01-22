<?php

namespace Loglia\LaravelClient\Monolog;

use Monolog\Handler\AbstractProcessingHandler;

class LogliaMonologV2Handler extends AbstractProcessingHandler
{
    use HandlesLogs;

    protected function write(array $record): void
    {
        $this->sendToLoglia($record);
    }
}
