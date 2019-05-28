<?php

namespace Retrospekt\LaravelClient;

use Monolog\Handler\AbstractProcessingHandler;

class RetrospektHandler extends AbstractProcessingHandler
{
    /**
     * Logging payloads above this size will not be sent.
     * Currently 5 MiB.
     */
    const MAX_PAYLOAD_SIZE = 5242880;

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
     * @throws RetrospektException
     */
    public function write(array $record)
    {
        $this->checkPayloadSize($record);

        // TODO: gzip payload
        $this->send($record['formatted']);

        dd('Record was sent', json_decode($record['formatted'], true));
    }

    /**
     * Throws an exception if the logging payload is too large to be sent to Retrospekt.
     *
     * @param array $record
     * @throws RetrospektException
     */
    private function checkPayloadSize(array $record)
    {
        if ($size = strlen($record['formatted']) > static::MAX_PAYLOAD_SIZE) {
            throw new RetrospektException(
                sprintf(
                    'Logging payload too large. Must be %d bytes or less, was %d bytes',
                    static::MAX_PAYLOAD_SIZE,
                    $size
                )
            );
        }
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
