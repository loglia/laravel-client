<?php

namespace Loglia\LaravelClient\Monolog;

use Monolog\Logger;
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
     * @var resource|null
     */
    private $socket;

    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        if (!$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
            throw new LogliaException(
                sprintf(
                    'Failed to open socket connection to logging server: %s',
                    socket_strerror(socket_last_error())
                )
            );
        }
    }

    public function close()
    {
        parent::close();

        if (is_resource($this->socket)) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }

    public function setApiKey(string $apiKey)
    {
        $this->apiKey = $apiKey;
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
     * Sends the log to Loglia.
     *
     * @param string $log
     * @throws LogliaException
     */
    private function send(string $log)
    {
        $endpoint = parse_url($this->endpoint);

        $hash = substr(hash('sha256', $log), 0, 32);
        $parts = str_split($log, 441);

        $sequence = 0;
        foreach ($parts as $part) {
            $message = $this->apiKey . $hash . sprintf('%03d', $sequence) . $part;
            dd($this->apiKey, $hash, sprintf('%03d', $sequence), $part);
            socket_sendto($this->socket, $message, strlen($message), 0, $endpoint['host'], $endpoint['port']);

            $sequence++;
        }
    }
}
