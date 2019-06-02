<?php

namespace Retrospekt\LaravelClient\Monolog;

use Monolog\Formatter\NormalizerFormatter;
use Retrospekt\LaravelClient\Exceptions\RetrospektException;
use Retrospekt\LaravelClient\Monolog\Formatting\RemoveChannel;
use Retrospekt\LaravelClient\Monolog\Formatting\RemoveLevelName;
use Retrospekt\LaravelClient\Monolog\Formatting\MoveExceptionToExtra;
use Retrospekt\LaravelClient\Monolog\Formatting\NormalizeContextData;
use Retrospekt\LaravelClient\Monolog\Formatting\MoveDatetimeToTimestamp;
use Retrospekt\LaravelClient\Monolog\Formatting\SerializeToJson;

class RetrospektFormatter extends NormalizerFormatter
{
    private $stages = [
        NormalizeContextData::class,
        MoveDatetimeToTimestamp::class,
        RemoveLevelName::class,
        RemoveChannel::class,
        MoveExceptionToExtra::class,
        SerializeToJson::class
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
}
