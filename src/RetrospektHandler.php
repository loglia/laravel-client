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
        // TODO: gzip payload
        // TODO: check size of payload, if too big, don't send
        $this->send($record['formatted']);

        dd('Record was sent', json_decode($record['formatted'], true));
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
