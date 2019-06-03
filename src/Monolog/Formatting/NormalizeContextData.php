<?php

namespace Retrospekt\LaravelClient\Monolog\Formatting;

use Retrospekt\LaravelClient\Exceptions\RetrospektException;

class NormalizeContextData implements Formatter
{
    /**
     * @inheritdoc
     */
    public function __invoke(array $record)
    {
        $record['context'] = $this->normalize($record['context']);

        return $record;
    }

    /**
     * Normalizes $data into easily serializable properties.
     *
     * @param $data
     * @param int $depth
     * @return array|string
     */
    private function normalize($data, $depth = 0)
    {
        if ($depth > 9) {
            return 'Over 9 levels deep, aborting normalization';
        }

        if (null === $data || is_scalar($data)) {
            if (is_float($data)) {
                if (is_infinite($data)) {
                    return ($data > 0 ? '' : '-') . 'INF';
                }
                if (is_nan($data)) {
                    return 'NaN';
                }
            }

            return $data;
        }

        if (is_array($data)) {
            $normalized = array();

            $count = 1;
            foreach ($data as $key => $value) {
                if ($count++ > 1000) {
                    $normalized['...'] = 'Over 1000 items ('.count($data).' total), aborting normalization';
                    break;
                }

                $normalized[$key] = $this->normalize($value, $depth+1);
            }

            return $normalized;
        }

        if ($data instanceof \DateTime) {
            return $data->format(\DateTime::ISO8601);
        }

        if (is_object($data)) {
            if ($data instanceof Exception || (PHP_VERSION_ID > 70000 && $data instanceof \Throwable)) {
                return $this->normalizeException($data);
            }

            // non-serializable objects that implement __toString stringified
            if (method_exists($data, '__toString') && !$data instanceof \JsonSerializable) {
                $value = $data->__toString();
            } else {
                // the rest is json-serialized in some way
                $value = $this->toJson($data, true);
            }

            return sprintf("[object] (%s: %s)", $this->getClass($data), $value);
        }

        if (is_resource($data)) {
            return sprintf('[resource] (%s)', get_resource_type($data));
        }

        return '[unknown('.gettype($data).')]';
    }

    /**
     * Normalizes an exception into a format expected by Retrospekt.
     *
     * @param \Exception|\Throwable $e
     * @return array
     * @throws RetrospektException
     */
    private function normalizeException($e)
    {
        if (!$e instanceof \Exception && !$e instanceof \Throwable) {
            throw new RetrospektException('Exception/Throwable expected');
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

    /**
     * Serializes a context data property to JSON.
     *
     * @param $data
     * @return false|string
     * @throws RetrospektException
     */
    private function toJson($data)
    {
        $json = json_encode($data);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RetrospektException('Failed to serialize context data property to JSON');
        }

        return $json;
    }

    /**
     * Gets the class of an object.
     *
     * @param $object
     * @return string
     */
    private function getClass($object)
    {
        $class = \get_class($object);

        return 'c' === $class[0] && 0 === strpos($class, "class@anonymous\0") ? get_parent_class($class).'@anonymous' : $class;
    }
}
