<?php

namespace Loglia\LaravelClient\Tests\Unit\Sticky;

use PHPUnit\Framework\TestCase;
use Loglia\LaravelClient\Sticky\StickyContext;

class StickyContextTest extends TestCase
{
    public function setUp()
    {
        // Clear the sticky context before each test run.
        StickyContext::clear();
    }

    /**
     * @test
     */
    public function it_can_have_sticky_context_added()
    {
        StickyContext::add('foo', 'bar');

        $this->assertSame(['foo' => 'bar'], StickyContext::all(), 'Was expecting sticky context to have foo => bar');
    }

    /**
     * @test
     */
    public function it_clears_away_context_data()
    {
        StickyContext::add('foo', 'bar');
        StickyContext::clear();

        $this->assertSame([], StickyContext::all(), 'Was expecting sticky context to be empty');
    }
}
