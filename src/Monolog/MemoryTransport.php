<?php

namespace Loglia\LaravelClient\Monolog;

use Loglia\LaravelClient\Exceptions\LogliaException;

class MemoryTransport implements TransportInterface
{
    /**
     * @var array
     */
    public $logs = [];

    public function send(string $log)
    {
        $this->logs[] = $log;
    }

    public function close()
    {
        $this->logs = [];
    }
}
