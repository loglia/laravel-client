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
    public function setEndpoint(string $endpoint): void
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Sets the API key for authenticated requests when sending logs.
     *
     * @param string $apiKey
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Allows for us to pretend to send the log message. Useful for testing purposes.
     *
     * @param bool $pretend
     */
    public function setPretend(bool $pretend): void
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
    public function write(array $record): void
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
    private function checkPayloadSize(array $record): void
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
    private function send(string $postData): void
    {
        $parts = [
            'curl',
            '-H',
            "'Authorization: Bearer {$this->apiKey}'",
            '-H',
            "'Content-Type: application/json'",
            '-A',
            $this->escapeArgument($this->getUserAgent()),
            '-X POST',
            '-d',
            $this->escapeArgument($postData),
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
        return 'Loglia Laravel Client v1.0.2';
    }

    /**
     * Escapes a shell argument.
     *
     * Extracted from: https://github.com/symfony/process/blob/v4.2.9/Process.php#L1613-L1633
     * All credit goes to the original developers.
     *
     * @param string $argument
     * @return string
     */
    private function escapeArgument(string $argument): string
    {
        if ($argument === '' || $argument === null) {
            return '""';
        }

        if (\DIRECTORY_SEPARATOR !== '\\') {
            return "'".str_replace("'", "'\\''", $argument)."'";
        }

        if (strpos($argument, "\0") !== false) {
            $argument = str_replace("\0", '?', $argument);
        }

        if (! preg_match('/[\/()%!^"<>&|\s]/', $argument)) {
            return $argument;
        }

        $argument = preg_replace('/(\\\\+)$/', '$1$1', $argument);

        return '"'.str_replace(['"', '^', '%', '!', "\n"], ['""', '"^^"', '"^%"', '"^!"', '!LF!'], $argument).'"';
    }
}
