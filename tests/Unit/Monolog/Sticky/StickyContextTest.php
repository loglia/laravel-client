<?php

namespace Loglia\LaravelClient\Tests\Unit\Monolog;

use PHPUnit\Framework\TestCase;
use Loglia\LaravelClient\Monolog\Sticky\StickyContext;

class StickyContextTest extends TestCase
{
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
