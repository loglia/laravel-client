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

        $expected = "POST / HTTP/1.1\r\n";
        $expected .= "Host: logs.loglia.app\r\n";
        $expected .= "User-Agent: Loglia Laravel Client v2.2.0\r\n";
        $expected .= "Authorization: Bearer abc123\r\n";
        $expected .= "Content-Length: 18\r\n";
        $expected .= "Content-Type: application/json\r\n\r\n";
        $expected .= "{\"hello\", \"world\"}";

        $this->assertSame($expected, $handler->getLastRequest());
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

        $expected = "POST / HTTP/1.1\r\n";
        $expected .= "Host: example.org\r\n";
        $expected .= "User-Agent: Loglia Laravel Client v2.2.0\r\n";
        $expected .= "Authorization: Bearer abc123\r\n";
        $expected .= "Content-Length: 18\r\n";
        $expected .= "Content-Type: application/json\r\n\r\n";
        $expected .= "{\"hello\", \"world\"}";

        $this->assertSame($expected, $handler->getLastRequest());
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

        $expected = "POST / HTTP/1.1\r\n";
        $expected .= "Host: logs.loglia.app\r\n";
        $expected .= "User-Agent: Loglia Laravel Client v2.2.0\r\n";
        $expected .= "Authorization: Bearer abc123\r\n";
        $expected .= "Content-Length: 33\r\n";
        $expected .= "Content-Type: application/json\r\n\r\n";
        $expected .= "{\"unicode\", \"§Ĭɮڡউ█👍\"}";

        $this->assertSame($expected, $handler->getLastRequest());
    }
}
