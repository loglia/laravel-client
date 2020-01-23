<?php

namespace Loglia\LaravelClient\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Loglia\LaravelClient\Exceptions\LogliaException;

class LogliaHandler extends AbstractProcessingHandler
{
    /**
     * Logging payloads above this size will not be sent. Currently 100 KiB.
     */
    const MAX_PAYLOAD_SIZE = 102400;

    /**
     * The endpoint to send logs to.
     *
     * @var string
     */
    private $endpoint = 'https://logs.loglia.app';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * Determines whether we pretend to send the log message.
     *
     * @var bool
     */
    private $pretend = false;

    /**
     * The last cURL command executed.
     *
     * @var string|null
     */
    private $lastCommand = null;

    /**
     * Allows the endpoint to send logs to be overridden if desired (e.g. for testing purposes).
     *
     * @param string $endpoint
     */
    public function setEndpoint(string $endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Sets the API key for authenticated requests when sending logs.
     *
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Allows for us to pretend to send the log message. Useful for testing purposes.
     *
     * @param bool $pretend
     */
    public function setPretend(bool $pretend)
    {
        $this->pretend = $pretend;
    }

    /**
     * Returns the last cURL command executed.
     *
     * @return string
     */
    public function getLastCommand(): string
    {
        return $this->lastCommand;
    }

    /**
     * Sends the log message to Loglia.
     *
     * @param array $record
     * @throws LogliaException
     * @return string
     */
    public function write(array $record)
    {
        $this->checkPayloadSize($record);

        $this->send($record['formatted']);
    }

    /**
     * Throws an exception if the logging payload is too large to be sent to Loglia.
     *
     * @param array $record
     * @throws LogliaException
     */
    private function checkPayloadSize(array $record)
    {
        if (($size = strlen($record['formatted'])) > static::MAX_PAYLOAD_SIZE) {
            throw new LogliaException(
                sprintf(
                    'Log payload too large. Must be %d bytes or less, was %d bytes',
                    static::MAX_PAYLOAD_SIZE,
                    $size
                )
            );
        }
    }

    /**
     * Sends the log to Loglia using an asynchronous cURL command.
     *
     * @param string $postData
     * @return string
     */
    private function send(string $postData)
    {
        $parts = [
            'curl',
            '-H',
            escapeshellarg('Authorization: Bearer '.$this->apiKey),
            '-H',
            escapeshellarg('Content-Type: application/json'),
            '-A',
            escapeshellarg($this->getUserAgent()),
            '-X POST',
            '-d',
            escapeshellarg($postData),
            $this->endpoint,
            '> /dev/null 2>&1 &'
        ];

        $cmd = implode(' ', $parts);

        if (! $this->pretend) {
            exec($cmd);
        }

        $this->lastCommand = $cmd;
    }

    /**
     * Returns the user agent string to send with the log.
     *
     * @return string
     */
    private function getUserAgent(): string
    {
        return 'Loglia Laravel Client v2.1.2';
    }
}
