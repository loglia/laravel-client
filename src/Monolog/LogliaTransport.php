<?php

namespace Loglia\LaravelClient\Monolog;

use Loglia\LaravelClient\Exceptions\LogliaException;

class LogliaTransport implements TransportInterface
{
    /**
     * @var string
     */
    private $endpoint = 'https://logs.loglia.xyz';

    /**
     * @var string
     */
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function send(array $logs)
    {
        $postData = json_encode($logs, JSON_THROW_ON_ERROR);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            sprintf('Authorization: Bearer %s', $this->apiKey),
            'Content-Type: application/json'
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
