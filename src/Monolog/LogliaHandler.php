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
    private $endpoint = 'logs-udp.loglia.app:1065';

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
     * The last HTTP request sent.
     *
     * @var string|null
     */
    private $lastRequest = null;

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
     * Returns the last HTTP request sent.
     *
     * @return string
     */
    public function getLastRequest(): string
    {
        return $this->lastRequest;
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

        // TODO: add application API key and package version

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
     * Sends the log to Loglia.
     *
     * @param string $log
     * @throws LogliaException
     */
    private function send(string $log)
    {
        $endpoint = parse_url($this->endpoint);

        $hash = hash('sha256', $log);
        $parts = str_split($log, 441);

        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if (!$socket) {
            throw new LogliaException(
                sprintf(
                    'Failed to open socket connection to logging server: %s',
                    socket_strerror(socket_last_error())
                )
            );
        }

        $sequence = 0;
        foreach ($parts as $part) {
            $message = $hash . sprintf('%03d', $sequence) . $part;
            socket_sendto($socket, $message, strlen($message), 0, $endpoint['host'], $endpoint['port']);

            $sequence++;
        }

        socket_close($socket);
    }
}
