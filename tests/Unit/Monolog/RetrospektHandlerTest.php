<?php

namespace Retrospekt\LaravelClient\Tests\Unit\Monolog;

use PHPUnit\Framework\TestCase;
use Retrospekt\LaravelClient\Monolog\RetrospektHandler;

class RetrospektHandlerTest extends TestCase
{
    /**
     * @test
     * @expectedException Retrospekt\LaravelClient\Exceptions\RetrospektException
     *@expectedExceptionMessage Log payload too large. Must be 102400 bytes or less, was 102401 bytes
     */
    public function it_throws_exception_if_payload_is_over_max_size()
    {
        $handler = new RetrospektHandler;
        $handler->setPretend(true);

        $handler->write([
            'message' => 'Hello world',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'local',
            'datetime' => new \DateTime,
            'extra' => [],
            'formatted' => str_repeat('a', RetrospektHandler::MAX_PAYLOAD_SIZE + 1)
        ]);
    }

    /** @test */
    public function it_sends_the_log_to_retrospekt_by_default()
    {
        $handler = new RetrospektHandler;
        $handler->setPretend(true);

        $cmd = $handler->write([
            'message' => 'Hello world',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'local',
            'datetime' => new \DateTime,
            'extra' => [],
            'formatted' => '{"hello", "world"}'
        ]);

        $this->assertSame("curl -A 'Retrospekt Laravel Client v1.0.0' -X POST -d '{\"hello\", \"world\"}' https://logs.retrospekt.io > /dev/null 2>&1 &", $cmd);
    }

    /** @test */
    public function it_sends_the_log_to_different_endpoint_when_configured()
    {
        $handler = new RetrospektHandler;
        $handler->setPretend(true);
        $handler->setEndpoint('https://example.org');

        $cmd = $handler->write([
            'message' => 'Hello world',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'local',
            'datetime' => new \DateTime,
            'extra' => [],
            'formatted' => '{"hello", "world"}'
        ]);

        $this->assertSame("curl -A 'Retrospekt Laravel Client v1.0.0' -X POST -d '{\"hello\", \"world\"}' https://example.org > /dev/null 2>&1 &", $cmd);
    }

    /** @test */
    public function it_supports_unicode_characters_in_logging_payload()
    {
        $handler = new RetrospektHandler;
        $handler->setPretend(true);

        $cmd = $handler->write([
            'message' => 'Hello world',
            'context' => [],
            'level' => 200,
            'level_name' => 'INFO',
            'channel' => 'local',
            'datetime' => new \DateTime,
            'extra' => [],
            'formatted' => '{"unicode", "👍"}'
        ]);

        $this->assertSame("curl -A 'Retrospekt Laravel Client v1.0.0' -X POST -d '{\"unicode\", \"👍\"}' https://logs.retrospekt.io > /dev/null 2>&1 &", $cmd);
    }
}
