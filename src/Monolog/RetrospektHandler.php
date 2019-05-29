<?php

namespace Retrospekt\LaravelClient\Monolog;

use Monolog\Handler\AbstractProcessingHandler;
use Retrospekt\LaravelClient\Exceptions\RetrospektException;

class RetrospektHandler extends AbstractProcessingHandler
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
    private $endpoint = 'https://logs.retrospekt.io';

    /**
     * Determines whether we pretend to send the log message.
     *
     * @var bool
     */
    private $pretend = false;

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
     * Allows for us to pretend to send the log message. Useful for testing purposes.
     *
     * @param $pretend
     */
    public function setPretend($pretend)
    {
        $this->pretend = $pretend;
    }

    /**
     * Sends the log message to Retrospekt.
     *
     * @param array $record
     * @throws RetrospektException
     * @return string
     */
    public function write(array $record)
    {
        $this->checkPayloadSize($record);

        return $this->send($record['formatted']);
    }

    /**
     * Throws an exception if the logging payload is too large to be sent to Retrospekt.
     *
     * @param array $record
     * @throws RetrospektException
     */
    private function checkPayloadSize(array $record)
    {
        if (($size = strlen($record['formatted'])) > static::MAX_PAYLOAD_SIZE) {
            throw new RetrospektException(
                sprintf(
                    'Log payload too large. Must be %d bytes or less, was %d bytes',
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
     * @return string
     */
    private function send($postData)
    {
        $parts = [
            'curl',
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

        return $cmd;
    }

    /**
     * Returns the user agent string to send with the log.
     *
     * @return string
     */
    private function getUserAgent()
    {
        return 'Retrospekt Laravel Client v1.0.0';
    }

    /**
     * Escapes a shell argument.
     *
     * Extracted from: https://github.com/symfony/process/blob/v4.2.9/Process.php#L1613-L1633
     * All credit goes to the original developers.
     *
     * @param $argument
     * @return string
     */
    private function escapeArgument($argument)
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
