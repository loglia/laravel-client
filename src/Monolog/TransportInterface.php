<?php

namespace Loglia\LaravelClient\Monolog;

interface TransportInterface
{
    public function send(string $message);

    public function close();
}
