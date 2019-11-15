<?php

namespace Loglia\LaravelClient\Sticky;

class StickyContextProcessor
{
    public function __invoke(array $record): array
    {
        if (empty($stickyContext = StickyContext::all())) {
            return $record;
        }

        $record['context'] = array_merge_recursive($record['context'], $stickyContext);

        return $record;
    }
}
