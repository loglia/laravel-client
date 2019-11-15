<?php

namespace Loglia\LaravelClient\Monolog\Formatting;

class MoveExceptionToExtra implements Formatter
{
    /**
     * @inheritdoc
     */
    public function __invoke(array $record)
    {
        if ($this->recordHasException($record)) {
            return $this->moveExceptionToExtra($record);
        }

        return $record;
    }

    /**
     * Determines if the record has an exception in its context data.
     *
     * @param array $record
     * @return bool
     */
    private function recordHasException(array $record)
    {
        if (empty($record['context']['exception'])) {
            return false;
        }

        return true;
    }

    /**
     * Moves an exception from the context data to the extra data.
     *
     * @param array $record
     * @return array
     */
    private function moveExceptionToExtra(array $record)
    {
        $record['extra']['__loglia']['exception'] = $record['context']['exception'];

        unset($record['context']['exception']);

        return $record;
    }
}
