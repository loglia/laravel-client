<?php

namespace Loglia\LaravelClient\Monolog;

use Loglia\LaravelClient\Exceptions\LogliaException;

class LogliaTransport implements TransportInterface
{
    /**
     * @var string
     */
    private $endpoint = 'logs-udp.loglia.app:1065';

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var null|resource
     */
    private $socket;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        if (!$this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) {
            throw new LogliaException(
                sprintf(
                    'Failed to open socket: %s',
                    socket_strerror(socket_last_error())
                )
            );
        }
    }

    public function send(string $log)
    {
        $endpoint = parse_url($this->endpoint);

        $hash = substr(hash('sha256', $log), 0, 32);
        $parts = str_split($log, 441);

        $sequence = 0;
        foreach ($parts as $part) {
            $message = $this->apiKey . $hash . sprintf('%03d', $sequence) . $part;
            socket_sendto($this->socket, $message, strlen($message), 0, $endpoint['host'], $endpoint['port']);

            $sequence++;
        }
    }

    public function close()
    {
        if (is_resource($this->socket)) {
            socket_close($this->socket);
            $this->socket = null;
        }
    }
}
