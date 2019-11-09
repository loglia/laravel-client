<?php

namespace Loglia\LaravelClient\Monolog;

use Monolog\Formatter\NormalizerFormatter;
use Loglia\LaravelClient\Monolog\Formatting\RemoveChannel;
use Loglia\LaravelClient\Monolog\Formatting\RemoveLevelName;
use Loglia\LaravelClient\Monolog\Formatting\SerializeToJson;
use Loglia\LaravelClient\Monolog\Formatting\AddTypeIfMissing;
use Loglia\LaravelClient\Monolog\Formatting\MoveExceptionToExtra;
use Loglia\LaravelClient\Monolog\Formatting\NormalizeContextData;
use Loglia\LaravelClient\Monolog\Formatting\MoveDatetimeToTimestamp;

class LogliaFormatter extends NormalizerFormatter
{
    private $stages = [
        NormalizeContextData::class,
        MoveDatetimeToTimestamp::class,
        RemoveLevelName::class,
        RemoveChannel::class,
        MoveExceptionToExtra::class,
        AddTypeIfMissing::class,
        SerializeToJson::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        // TODO: write a stage that takes any context in `--loglia` and moved it to extra
        foreach ($this->stages as $stage) {
            $record = (new $stage)($record);
        }

        return $record;
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
