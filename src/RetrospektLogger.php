<?php

namespace Retrospekt\LaravelClient;

use Monolog\Logger;

class RetrospektLogger
{
    /**
     * Create a Monolog instance to send logs to Retrospekt.
     *
     * @param array $config
     * @return Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('retrospekt');

        $handler = new RetrospektHandler;

        if (config('retrospekt.endpoint')) {
            $handler->setEndpoint(config('retrospekt.endpoint'));
        }

        $handler->setFormatter(new RetrospektFormatter(\DateTime::ISO8601));

        $logger->pushHandler($handler);

        return $logger;
    }
}
