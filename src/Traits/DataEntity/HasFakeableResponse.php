<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\MockResponseSequence;
use BitMx\DataEntities\Testing\MockClient;
use Closure;
use LogicException;

/**
 * @mixin DataEntity
 */
trait HasFakeableResponse
{
    protected static bool $fake = false;

    protected static ?MockClient $mockClient = null;

    /**
     * @var array<class-string, MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse>
     */
    protected static array $mockResponses = [];

    /**
     * @param  array<class-string, MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse>  $mockResponses
     */
    public static function fake(array $mockResponses = []): MockClient
    {
        static::$fake = true;
        static::$mockClient = new MockClient($mockResponses);
        static::$mockResponses = $mockResponses;
        static::$assertions = [];
        static::$recordedParameters = [];

        return static::$mockClient;
    }

    public static function isFake(): bool
    {
        return static::$fake;
    }

    public static function getMockClient(): MockClient
    {
        if (static::$mockClient === null) {
            throw new LogicException('No mock client is active. Call DataEntity::fake() first.');
        }

        return static::$mockClient;
    }

    public static function resetMock(): void
    {
        static::$mockResponses = [];
        static::$assertions = [];
        static::$recordedParameters = [];
        static::$fake = false;
        static::$mockClient = null;
    }
}
