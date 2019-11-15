<?php

namespace Loglia\LaravelClient\Sticky;

class StickyContext
{
    /**
     * Holds the static context entries.
     *
     * @var array
     */
    protected static $context = [];

    /**
     * Add a sticky context item.
     *
     * @param string $key
     * @param $value
     */
    public static function add(string $key, $value): void
    {
        static::$context[$key] = $value;
    }

    /**
     * Returns all sticky context items.
     *
     * @return array
     */
    public static function all(): array
    {
        return static::$context;
    }

    /**
     * Clears away all sticky context data.
     */
    public static function clear(): void
    {
        static::$context = [];
    }
}
