<?php

namespace Retrospekt\LaravelClient;

use Monolog\Formatter\NormalizerFormatter;

class RetrospektFormatter extends NormalizerFormatter
{
    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $normalized = $this->normalize($record);

        // TODO: move exceptions to --retrospekt

        $encoded = json_encode($normalized);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Unable to serialize log message as JSON');
        }

        return $encoded;
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
     * Normalizes an exception into a format expected by Retrospekt.
     *
     * @param \Exception|\Throwable $e
     * @return array
     */
    protected function normalizeException($e)
    {
        if (!$e instanceof \Exception && !$e instanceof \Throwable) {
            throw new \InvalidArgumentException('Exception/Throwable expected');
        }

        $exception = [
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $this->traceForException($e->getTrace(), $e->getFile(), $e->getLine())
        ];

        foreach ($e->getTrace() as $trace) {
            $exception['trace'][] = [
                'file' => $trace['file'] ?? null,
                'line' => $trace['line'] ?? null,
                'function' => $trace['function'] ?? null,
                'class' => $trace['class'] ?? null
            ];
        }

        if ($e->getPrevious()) {
            $exception['previous'] = $this->normalizeException($e->getPrevious());
        }

        return $exception;
    }

    /**
     * Normalizes a stack trace for an exception.
     *
     * @param array $trace
     * @param string $file
     * @param int $line
     * @return array
     */
    private function traceForException(array $trace, string $file, int $line)
    {
        $stacktrace = [];

        foreach ($trace as $frame) {
            $stacktrace[] = $this->getFrame($file, $line, $frame);

            $file = $frame['file'] ?? '[internal]';
            $line = $frame['line'] ?? 0;
        }

        $stacktrace[] = $this->getFrame($file, $line, []);

        return $stacktrace;
    }

    /**
     * Normalizes a stacktrace frame.
     *
     * @param string $file
     * @param int $line
     * @param array $stacktraceFrame
     * @return array
     */
    private function getFrame(string $file, int $line, array $stacktraceFrame)
    {
        if (preg_match('/^(.*)\((\d+)\) : (?:eval\(\)\'d code|runtime-created function)$/', $file, $matches)) {
            $file = $matches[1];
            $line = (int) $matches[2];
        }

        if (isset($stacktraceFrame['class'])) {
            $functionName = sprintf('%s::%s', $stacktraceFrame['class'], $stacktraceFrame['function']);
        } elseif (isset($stacktraceFrame['function'])) {
            $functionName = $stacktraceFrame['function'];
        } else {
            $functionName = null;
        }

        return [
            'function' => $functionName,
            'file' => $file,
            'line' => $line
        ];
    }
}
