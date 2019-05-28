<?php

namespace Retrospekt\LaravelClient;

use Monolog\Handler\AbstractProcessingHandler;

class RetrospektHandler extends AbstractProcessingHandler
{
    /**
     * The endpoint to send logs to.
     *
     * @var string
     */
    private $endpoint = 'https://logs.retrospekt.io';

    /**
     * Allows the endpoint to send logs to be overridden if desired (e.g. for testing purposes).
     *
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

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
            $this->endpoint,
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
