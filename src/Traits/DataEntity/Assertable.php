<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\DataEntity;
use Closure;
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
     * @var array<class-string, list<array<array-key, mixed>>>
     */
    public static array $recordedParameters = [];

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
        return array_key_exists($class, static::$assertions);
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

    /**
     * @param  class-string  $class
     */
    public static function assertNotExecuted(string $class): void
    {
        Assert::assertFalse(static::classInAssertExists($class), 'The query was executed');
    }

    /**
     * @param  class-string  $class
     * @param  array<array-key, mixed>|Closure(array<array-key, mixed>): bool  $expected
     */
    public static function assertExecutedWith(string $class, array|Closure $expected): void
    {
        Assert::assertTrue(
            array_key_exists($class, static::$recordedParameters) && static::$recordedParameters[$class] !== [],
            'The query was not executed'
        );

        $matched = false;

        foreach (static::$recordedParameters[$class] as $parameters) {
            if ($expected instanceof Closure) {
                if ($expected($parameters)) {
                    $matched = true;
                    break;
                }

                continue;
            }

            if (collect($expected)->every(fn (mixed $value, mixed $key): bool => Arr::get($parameters, $key) === $value)) {
                $matched = true;
                break;
            }
        }

        Assert::assertTrue($matched, 'The query was not executed with the expected parameters');
    }
}
