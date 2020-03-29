<?php

namespace Loglia\LaravelClient\Tests\Unit\Monolog;

use Illuminate\Support\Facades\Log;
use Loglia\LaravelClient\Monolog\MemoryTransport;
use PHPUnit\Framework\TestCase;
use Loglia\LaravelClient\Monolog\LogliaHandler;

class LogliaHandlerTest extends TestCase
{
    /**
     * @var MemoryTransport
     */
    private $transport;

    /**
     * @var LogliaHandler
     */
    private $handler;

    public function setUp()
    {
        parent::setUp();

        $this->transport = new MemoryTransport;
        $this->handler = new LogliaHandler($this->transport);
    }

    /**
     * @test
     * @expectedException Loglia\LaravelClient\Exceptions\LogliaException
     * @expectedExceptionMessage Log payload too large. Must be 102400 bytes or less, was 102401 bytes
     */
    public function it_throws_exception_if_payload_is_over_max_size()
    {
        $this->handler->write([
            'message' => 'Hello world',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'local',
            'datetime' => new \DateTime,
            'extra' => [],
            'formatted' => str_repeat('a', LogliaHandler::MAX_PAYLOAD_SIZE + 1)
        ]);
    }

    /** @test */
    public function it_sends_logs_through_the_transport()
    {
        $this->handler->write([
            'message' => 'Hello world',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'local',
            'datetime' => new \DateTime,
            'extra' => [],
            'formatted' => '{"foo":"bar"}'
        ]);

        $this->handler->write([
            'message' => 'Hello world',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'local',
            'datetime' => new \DateTime,
            'extra' => [],
            'formatted' => '{"bin":"baz"}'
        ]);

        $this->assertCount(2, $this->transport->logs);
        $this->assertSame('{"foo":"bar"}', $this->transport->logs[0]);
        $this->assertSame('{"bin":"baz"}', $this->transport->logs[1]);
    }
}
