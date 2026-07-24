<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\MockResponseSequence;
use BitMx\DataEntities\Testing\MockClient;
use BitMx\DataEntities\Testing\RecordedExecution;
use Closure;

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
    public static function fake(array $mockResponses = []): void
    {
        static::$fake = true;
        static::$mockClient = new MockClient($mockResponses);
        static::$mockResponses = $mockResponses;
        static::$assertions = [];
        static::$recordedParameters = [];
    }

    public static function isFake(): bool
    {
        return static::$fake;
    }

    public static function getMockClient(): MockClient
    {
        if (static::$mockClient === null) {
            static::$mockClient = new MockClient;
        }

        return static::$mockClient;
    }

    /**
     * @param  array<class-string, MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse>  $responses
     */
    public static function mock(array $responses): void
    {
        static::$fake = true;
        static::getMockClient()->mock($responses);
        static::$mockResponses = array_replace(static::$mockResponses, $responses);
    }

    /**
     * @param  MockResponse|Closure(PendingQuery): MockResponse  $response
     */
    public static function fallback(MockResponse|Closure $response): void
    {
        static::$fake = true;
        static::getMockClient()->fallback($response);
    }

    /**
     * @return list<RecordedExecution>
     */
    public static function recorded(?string $class = null): array
    {
        return static::getMockClient()->recorded($class);
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
