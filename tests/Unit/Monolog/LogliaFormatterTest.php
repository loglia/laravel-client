<?php

namespace Loglia\LaravelClient\Tests\Unit\Monolog;

use PHPUnit\Framework\TestCase;
use Loglia\LaravelClient\Monolog\LogliaFormatter;

class LogliaFormatterTest extends TestCase
{
    /**
     * @var LogliaFormatter
     */
    private $formatter;

    public function setUp()
    {
        $this->formatter = new LogliaFormatter(\DateTime::ISO8601);
    }

    /** @test */
    public function it_moved_loglia_object_to_extra()
    {
        $result = $this->formatter->format($this->generateLog());
        $decoded = json_decode($result, true);

        $this->assertArrayNotHasKey('--loglia', $decoded['context'], '--loglia should have been moved away from context.');
        $this->assertArrayHasKey('--loglia', $decoded['extra'], 'Extra data is meant to have --loglia key.');
    }

    /** @test */
    public function it_moves_datetime_to_timestamp()
    {
        $result = $this->formatter->format($this->generateLog());
        $decoded = json_decode($result, true);

        $this->assertSame($decoded['timestamp'], 1546354800000, 'Context data is meant to have timestamp key.');
        $this->assertArrayNotHasKey('datetime', $decoded, 'Log payload is not meant to have a datetime key.');
    }

    /** @test */
    public function it_moves_exceptions_to_extra()
    {
        $result = $this->formatter->format($this->generateLog());
        $decoded = json_decode($result, true);

        $this->assertArrayNotHasKey('exception', $decoded['context'], 'Context data is not meant to have exception key.');
        $this->assertArrayHasKey('--loglia', $decoded['extra'], 'Extra data is meant to have a --retrospekt key.');
        $this->assertArrayHasKey('exception', $decoded['extra']['--loglia'], 'Extra data is meant to have a --retrospekt.exception key.');
    }

    /** @test */
    public function it_normalizes_context_data()
    {
        $result = $this->formatter->format($this->generateLog());
        $decoded = json_decode($result, true);

        $this->assertSame(1, $decoded['context']['integer'], 'Integer log context did not return an integer.');
        $this->assertSame(1.23, $decoded['context']['float'], 'Float log context did not return a float.');
        $this->assertSame(true, $decoded['context']['boolean'], 'Boolean log context did not return a boolean.');
        $this->assertSame('hello world', $decoded['context']['string'], 'String log context did not return a string.');
        $this->assertSame(['hello' => 'world'], $decoded['context']['array'], 'Array log context did not return an array.');
        $this->assertSame('[object] (stdClass: {"foo":"bar"})', $decoded['context']['object'], 'Object log context was not returned in expected format.');
        $this->assertSame('[object] (Loglia\LaravelClient\Tests\Unit\Monolog\ClassWithToString: hello world)', $decoded['context']['objecttostring'], 'Object with __toString() context was not returned in expected format.');
        $this->assertSame('[resource] (stream)', $decoded['context']['resource'], 'Resource log context was not returned in expected format.');
    }

    /** @test */
    public function it_removes_channel_property()
    {
        $result = $this->formatter->format($this->generateLog());
        $decoded = json_decode($result, true);

        $this->assertArrayNotHasKey('channel', $decoded, 'channel still exists in log payload.');
    }

    /** @test */
    public function it_removes_level_name_property()
    {
        $result = $this->formatter->format($this->generateLog());
        $decoded = json_decode($result, true);

        $this->assertArrayNotHasKey('level_name', $decoded, 'level_name still exists in log payload.');
    }

    /** @test */
    public function it_adds_type_of_log_if_missing()
    {
        $result = $this->formatter->format($this->generateLog());
        $decoded = json_decode($result, true);

        $this->assertSame('log', $decoded['extra']['--loglia']['type'], 'extra.--loglia.type must be `log`.');
    }

    /** @test */
    public function it_serializes_the_log_to_json()
    {
        $result = $this->formatter->format($this->generateLog());

        json_decode($result);
        $this->assertSame(json_last_error(), JSON_ERROR_NONE, 'The log payload not returned in JSON format.');
    }

    private function generateLog()
    {
        $firstException = new \InvalidArgumentException('All method parameters must be integers');
        $secondException = new \OutOfBoundsException('Array index out of bounds', 0, $firstException);

        $object = new \stdClass;
        $object->foo = 'bar';

        $resource = fopen(tempnam('/tmp', 'loglia'), 'r');

        return [
            'message' => 'Second exception',
            'context' => [
                'exception' => $secondException,
                'integer' => 1,
                'float' => 1.23,
                'boolean' => true,
                'string' => 'hello world',
                'array' => ['hello' => 'world'],
                'object' => $object,
                'objecttostring' => new ClassWithToString,
                'resource' => $resource,
                '--loglia' => [
                    'foo' => 'bar'
                ]
            ],
            'level' => 400,
            'level_name' => 'ERROR',
            'channel' => 'local',
            'datetime' => new \DateTime('2019-01-01 15:00:00'),
            'extra' => []
        ];
    }
}

class ClassWithToString
{
    public function __toString()
    {
        return 'hello world';
    }
}
