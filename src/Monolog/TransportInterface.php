<?php

namespace Loglia\LaravelClient\Monolog;

interface TransportInterface
{
    public function send(array $logs);
}
