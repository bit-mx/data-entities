<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Testing\RecordedExecution;
use Closure;

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
     * @var array<class-string, list<array<array-key, mixed>>>
     */
    public static array $recordedParameters = [];

    /**
     * @param  class-string  $class
     */
    public static function assertExecuted(string $class): void
    {
        static::getMockClient()->assertExecuted($class);
    }

    /**
     * @param  class-string  $class
     */
    public static function assertExecutedOnce(string $class): void
    {
        static::getMockClient()->assertExecutedOnce($class);
    }

    /**
     * @param  class-string  $class
     */
    public static function assertExecutedCount(string $class, int $count): void
    {
        static::getMockClient()->assertExecutedCount($class, $count);
    }

    /**
     * @param  class-string  $class
     */
    public static function assertNotExecuted(string $class): void
    {
        static::getMockClient()->assertNotExecuted($class);
    }

    public static function assertNothingExecuted(): void
    {
        static::getMockClient()->assertNothingExecuted();
    }

    /**
     * @param  class-string  $class
     * @param  array<array-key, mixed>|Closure(array<array-key, mixed>): bool|Closure(RecordedExecution): bool  $expected
     */
    public static function assertExecutedWith(string $class, array|Closure $expected): void
    {
        static::getMockClient()->assertExecutedWith($class, $expected);
    }
}
