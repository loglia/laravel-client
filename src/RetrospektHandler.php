<?php

namespace Retrospekt\LaravelClient;

use Monolog\Handler\AbstractProcessingHandler;

class RetrospektHandler extends AbstractProcessingHandler
{
    /**
     * Sends the log message to Retrospekt.
     *
     * @param array $record
     */
    public function write(array $record)
    {
        $this->send($record['formatted']);

        dd('formatted record being sent:', json_decode($record['formatted'], true));

        // TODO: add User-Agent header of `Retrospekt Laravel Client {version}`. use getVersion() on SP
    }

    /**
     * Sends the log to Retrospekt using an asynchronous cURL command.
     *
     * @param $postData
     */
    private function send($postData)
    {
        $parts = [
            'curl',
            '-A',
            escapeshellarg($this->getUserAgent()),
            '-X POST',
            '-d',
            escapeshellarg($postData),
            'http://requestbin.fullcontact.com/vx5gfhvx',      // TODO: make this configurable, but dont put in config file
            '> /dev/null 2>&1 &'
        ];

        exec(implode(' ', $parts));
    }

    /**
     * Returns the user agent string to send with the log.
     *
     * @return string
     */
    private function getUserAgent()
    {
        return sprintf('Retrospekt Laravel Client v%s', LaravelClientServiceProvider::VERSION);
    }
}
