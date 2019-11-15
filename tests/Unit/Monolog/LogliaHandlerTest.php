<?php

namespace Loglia\LaravelClient\Tests\Unit\Monolog;

use PHPUnit\Framework\TestCase;
use Loglia\LaravelClient\Monolog\LogliaHandler;

class LogliaHandlerTest extends TestCase
{
    /**
     * @test
     * @expectedException Loglia\LaravelClient\Exceptions\LogliaException
     * @expectedExceptionMessage Log payload too large. Must be 102400 bytes or less, was 102401 bytes
     */
    public function it_throws_exception_if_payload_is_over_max_size()
    {
        $handler = new LogliaHandler;
        $handler->setPretend(true);

        $handler->write([
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
    public function it_sends_the_log_to_loglia_by_default()
    {
        $handler = new LogliaHandler;
        $handler->setPretend(true);
        $handler->setApiKey('abc123');

        $handler->write([
            'message' => 'Hello world',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'local',
            'datetime' => new \DateTime,
            'extra' => [],
            'formatted' => '{"hello", "world"}'
        ]);

        $this->assertSame("curl -H 'Authorization: Bearer abc123' -H 'Content-Type: application/json' -A 'Loglia Laravel Client v1.0.0' -X POST -d '{\"hello\", \"world\"}' https://logs.loglia.app > /dev/null 2>&1 &", $handler->getLastCommand());
    }

    /** @test */
    public function it_sends_the_log_to_different_endpoint_when_configured()
    {
        $handler = new LogliaHandler;
        $handler->setPretend(true);
        $handler->setEndpoint('https://example.org');
        $handler->setApiKey('abc123');

        $handler->write([
            'message' => 'Hello world',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'local',
            'datetime' => new \DateTime,
            'extra' => [],
            'formatted' => '{"hello", "world"}'
        ]);

        $this->assertSame("curl -H 'Authorization: Bearer abc123' -H 'Content-Type: application/json' -A 'Loglia Laravel Client v1.0.0' -X POST -d '{\"hello\", \"world\"}' https://example.org > /dev/null 2>&1 &", $handler->getLastCommand());
    }

    /** @test */
    public function it_supports_unicode_characters_in_logging_payload()
    {
        $handler = new LogliaHandler;
        $handler->setPretend(true);
        $handler->setApiKey('abc123');

        $handler->write([
            'message' => 'Hello world',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'local',
            'datetime' => new \DateTime,
            'extra' => [],
            'formatted' => '{"unicode", "§Ĭɮڡউ█👍"}'
        ]);

        $this->assertSame("curl -H 'Authorization: Bearer abc123' -H 'Content-Type: application/json' -A 'Loglia Laravel Client v1.0.0' -X POST -d '{\"unicode\", \"§Ĭɮڡউ█👍\"}' https://logs.loglia.app > /dev/null 2>&1 &", $handler->getLastCommand());
    }
}
