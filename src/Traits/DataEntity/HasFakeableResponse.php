<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\MockResponseSequence;
use Closure;

/**
 * @mixin DataEntity
 */
trait HasFakeableResponse
{
    protected static bool $fake = false;

    /**
     * @var array<class-string, MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse>
     */
    protected static array $mockResponses = [];

    /**
     * @param  array<class-string, MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse>  $mockResponses
     */
    public static function fake(array $mockResponses = []): void
    {
        static::$fake = true;

        static::$mockResponses = $mockResponses;
    }

    public static function isFake(): bool
    {
        return static::$fake;
    }

    public static function resetMock(): void
    {
        static::$mockResponses = [];
        static::$assertions = [];
        static::$recordedParameters = [];
        static::$fake = false;
    }
}
