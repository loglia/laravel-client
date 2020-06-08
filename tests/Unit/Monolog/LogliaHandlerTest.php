<?php

namespace Loglia\LaravelClient\Tests\Unit\Monolog;

use Loglia\LaravelClient\Monolog\LogliaFormatter;
use PHPUnit\Framework\TestCase;
use Loglia\LaravelClient\Monolog\LogliaHandler;
use Loglia\LaravelClient\Monolog\MemoryTransport;

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

    public function setUp(): void
    {
        parent::setUp();

        $this->transport = new MemoryTransport;
        $this->handler = new LogliaHandler($this->transport);
        $this->handler->setFormatter(new LogliaFormatter(\DateTime::ISO8601));
    }

    /** @test */
    public function it_sends_logs_through_the_transport()
    {
        $time = new \DateTime('2020-01-01');

        $this->handler->handleBatch([
            [
                'message' => 'Hello world',
                'context' => [],
                'level' => 200,
                'level_name' => 'INFO',
                'channel' => 'local',
                'datetime' => $time,
                'extra' => []
            ],
            [
                'message' => 'Hello world',
                'context' => [],
                'level' => 200,
                'level_name' => 'INFO',
                'channel' => 'local',
                'datetime' => $time,
                'extra' => []
            ],
        ]);

        $this->assertCount(2, $this->transport->logs);
        $this->assertSame('{"message":"Hello world","context":[],"level":200,"extra":{"__loglia":{"type":"log"}},"timestamp":1577836800000}', $this->transport->logs[0]);
        $this->assertSame('{"message":"Hello world","context":[],"level":200,"extra":{"__loglia":{"type":"log"}},"timestamp":1577836800000}', $this->transport->logs[1]);
    }
}
