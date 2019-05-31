<?php

namespace Retrospekt\LaravelClient\Monolog\Formatting;

class MoveDatetimeToTimestamp implements Formatter
{
    /**
     * @inheritdoc
     */
    public function __invoke(array $record)
    {
        if (empty($record['datetime'])) {
            return $record;
        }

        /** @var \DateTime $dateTime */
        $dateTime = $record['datetime'];

        if (
            version_compare(PHP_VERSION, '7.2.0', '<') &&
            $dateTime->format('v') == 1000
        ) {
            $record['timestamp'] = (int) ($record['datetime']->format('U') . '999');
        } else {
            $record['timestamp'] = (int) $record['datetime']->format('Uv');
        }

        unset($record['datetime']);

        return $record;
    }
}
