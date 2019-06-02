<?php

namespace Retrospekt\LaravelClient\Monolog\Formatting;

class SerializeToJson implements Formatter
{
    /**
     * @inheritdoc
     */
    public function __invoke(array $record)
    {
        $encoded = json_encode($record);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RetrospektException('Unable to serialize log message to JSON');
        }

        return $encoded;
    }
}
