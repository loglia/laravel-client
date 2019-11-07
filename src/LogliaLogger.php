<?php

namespace Loglia\LaravelClient;

use DateTime;
use Monolog\Logger;
use Loglia\LaravelClient\Monolog\LogliaHandler;
use Loglia\LaravelClient\Monolog\LogliaFormatter;

class LogliaLogger
{
    /**
     * Create a Monolog instance to send logs to Loglia.
     *
     * @param array $config
     * @return Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('loglia');

        $handler = new LogliaHandler;

        if (config('loglia.api_key')) {
            $handler->setApiToken(config('loglia.api_key'));
        }

        if (config('loglia.endpoint')) {
            $handler->setEndpoint(config('loglia.endpoint'));
        }

        $handler->setFormatter(new LogliaFormatter(DateTime::ISO8601));

        $logger->pushHandler($handler);

        return $logger;
    }
}
