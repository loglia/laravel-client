<?php

namespace Loglia\LaravelClient\Tests\Unit\Sticky;

use PHPUnit\Framework\TestCase;
use Loglia\LaravelClient\Sticky\StickyContext;
use Loglia\LaravelClient\Sticky\StickyContextProcessor;

class StickyContextProcessorTest extends TestCase
{
    public function setUp(): void
    {
        // Clear the sticky context before each test run.
        StickyContext::clear();
    }

    /**
     * @test
     */
    public function it_doesnt_change_the_record_context_when_no_sticky_context_was_added()
    {
        $record = [
            'context' => []
        ];

        $stickyContextProcessor = new StickyContextProcessor;

        $result = $stickyContextProcessor($record);

        $this->assertSame($record['context'], $result['context'], 'Context data should not have changed');
    }

    /**
     * @test
     */
    public function it_adds_sticky_context_data()
    {
        StickyContext::add('foo', 'bar');

        $stickyContextProcessor = new StickyContextProcessor;

        $result = $stickyContextProcessor([
            'context' => []
        ]);

        $this->assertSame(['foo' => 'bar'], $result['context'], 'Was expecting sticky context to contain foo => bar');
    }

    /**
     * @test
     */
    public function it_merges_existing_context_with_sticky_context()
    {
        StickyContext::add('--loglia', [
            'request' => [
                'uuid' => '27bc54de-7031-45e9-a59d-92ba3376cc05'
            ]
        ]);

        $stickyContextProcessor = new StickyContextProcessor;

        $result = $stickyContextProcessor([
            'context' => [
                '--loglia' => [
                    'foo' => 'bar'
                ]
            ]
        ]);

        $this->assertSame([
            '--loglia' => [
                'foo' => 'bar',
                'request' => [
                    'uuid' => '27bc54de-7031-45e9-a59d-92ba3376cc05'
                ]
            ]
        ], $result['context'], 'StickyContextProcessor should merge existing context with sticky context');
    }
}
