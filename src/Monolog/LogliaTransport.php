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
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer' . $this->apiKey,
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
