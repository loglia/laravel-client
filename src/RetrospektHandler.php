<?php

namespace Retrospekt\LaravelClient;

use Monolog\Handler\AbstractProcessingHandler;

class RetrospektHandler extends AbstractProcessingHandler
{
    /**
     * Sends the log message to Retrospekt.
     *
     * @param array $record
     */
    public function write(array $record)
    {
        dd('formatted record being sent:', json_decode($record['formatted'], true));

        // TODO: do the cURL stuff to send the log message. Perhaps use adapters so the user is able to configure which
        // driver they want to use. sync, async, etc.

        // TODO: add User-Agent header of `Retrospekt Laravel Client {version}`. use getVersion() on SP
    }
}
