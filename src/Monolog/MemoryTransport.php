<?php

namespace Loglia\LaravelClient\Monolog;

class MemoryTransport implements TransportInterface
{
    /**
     * @var array
     */
    public $logs = [];

    public function send(array $logs)
    {
        $this->logs = $logs;
    }
}
