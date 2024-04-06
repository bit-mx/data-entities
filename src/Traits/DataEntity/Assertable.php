<?php

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\DataEntity;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert;

/**
 * @mixin DataEntity
 */
trait Assertable
{
    /**
     * @backupStaticAttributes enabled
     *
     * @var array<class-string, int>
     */
    public static array $assertions = [];

    /**
     * @param  class-string  $class
     */
    public static function assertExecuted(string $class): void
    {
        Assert::assertTrue(static::classInAssertExists($class) && static::$assertions[$class] > 0, 'The query was not executed');
    }

    /**
     * @param  class-string  $class
     */
    protected static function classInAssertExists(string $class): bool
    {
        return Arr::has(static::$assertions, $class);
    }

    /**
     * @param  class-string  $class
     */
    public static function assertExecutedOnce(string $class): void
    {
        static::assertExecutedCount($class, 1);
    }

    /**
     * @param  class-string  $class
     */
    public static function assertExecutedCount(string $class, int $count): void
    {
        Assert::assertTrue(
            static::classInAssertExists($class) && static::$assertions[$class] === $count,
            'The query was not executed ',
        );
    }
}
