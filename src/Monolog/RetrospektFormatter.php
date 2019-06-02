<?php

namespace Retrospekt\LaravelClient\Monolog;

use Monolog\Formatter\NormalizerFormatter;
use Retrospekt\LaravelClient\Exceptions\RetrospektException;
use Retrospekt\LaravelClient\Monolog\Formatting\RemoveChannel;
use Retrospekt\LaravelClient\Monolog\Formatting\RemoveLevelName;
use Retrospekt\LaravelClient\Monolog\Formatting\MoveExceptionToExtra;
use Retrospekt\LaravelClient\Monolog\Formatting\NormalizeContextData;
use Retrospekt\LaravelClient\Monolog\Formatting\MoveDatetimeToTimestamp;

class RetrospektFormatter extends NormalizerFormatter
{
    private $stages = [
        NormalizeContextData::class,
        MoveDatetimeToTimestamp::class,
        RemoveLevelName::class,
        RemoveChannel::class,
        MoveExceptionToExtra::class
    ];

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        foreach ($this->stages as $stage) {
            $record = (new $stage)($record);
        }

        return $this->serializeToJson($record);
    }

    /**
     * {@inheritdoc}
     */
    public function formatBatch(array $records)
    {
        foreach ($records as $key => $record) {
            $records[$key] = $this->format($record);
        }

        return $records;
    }

    /**
     * Serializes the formatted log to JSON.
     *
     * @param array $record
     * @return string
     * @throws RetrospektException
     */
    private function serializeToJson(array $record)
    {
        $encoded = json_encode($record);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RetrospektException('Unable to serialize log message as JSON');
        }

        return $encoded;
    }
}
