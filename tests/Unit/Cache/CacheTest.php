<?php

declare(strict_types=1);

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\Cache\CachedResponse;
use BitMx\DataEntities\Cache\CacheDriver;
use BitMx\DataEntities\Cache\CacheHandler;
use BitMx\DataEntities\Cache\CacheKey;
use BitMx\DataEntities\Contracts\Cacheable;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Plugins\HasCache;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\RecordedResponse;
use ReflectionMethod;

use function Pest\Laravel\freezeTime;
use function Pest\Laravel\travelTo;

afterEach(function () {
    DataEntity::resetMock();
});

it('builds a deterministic cache key from the pending query', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return ['id' => 1];
        }

        protected function defaultOutputParameters(): array
        {
            return ['total' => 'INT'];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);
    $key = CacheKey::create($pendingQuery);

    expect($key)->toBe(CacheKey::create($pendingQuery))
        ->and($key)->toContain('sp_test')
        ->and($key)->toContain('"id":1')
        ->and($key)->toContain('"total":"INT"')
        ->and($key)->toContain('"connection"');
});

it('changes the cache key when the database connection changes', function () {
    $sqlsrvEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function resolveDatabaseConnection(): string
        {
            return 'sqlsrv';
        }
    };

    $mysqlEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function resolveDatabaseConnection(): string
        {
            return 'mysql';
        }
    };

    expect(CacheKey::create(new PendingQuery($sqlsrvEntity)))
        ->not->toBe(CacheKey::create(new PendingQuery($mysqlEntity)));
});

it('changes the cache key when parameters change', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $pendingQueryA = new PendingQuery($dataEntity);
    $pendingQueryA->parameters()->add('id', 1);

    $pendingQueryB = new PendingQuery($dataEntity);
    $pendingQueryB->parameters()->add('id', 2);

    expect(CacheKey::create($pendingQueryA))->not->toBe(CacheKey::create($pendingQueryB));
});

it('floors expired cache TTL to one second', function () {
    freezeTime();

    $dataEntity = new #[SingleItemResponse] class extends DataEntity implements Cacheable
    {
        use HasCache;

        public function resolveStoreProcedure(): string
        {
            return 'sp_ttl_past';
        }

        public function cacheExpiresAt(): int|DateTimeInterface
        {
            return now()->subMinute();
        }
    };

    $method = new ReflectionMethod($dataEntity, 'getCacheExpiresInSeconds');

    expect($method->invoke($dataEntity, new PendingQuery($dataEntity)))->toBe(1);
});

it('stores, retrieves and deletes cached responses with CacheDriver', function () {
    freezeTime();

    $driver = new CacheDriver(config('cache.default'));
    $cachedResponse = new CachedResponse(new RecordedResponse(['id' => 1], ['out' => 2]), 60);

    $driver->set('test-cache-key', $cachedResponse);

    $retrieved = $driver->get('test-cache-key');

    expect($retrieved)->toBeInstanceOf(CachedResponse::class)
        ->and($retrieved->recordedResponse->data())->toBe(['id' => 1])
        ->and($retrieved->recordedResponse->output())->toBe(['out' => 2])
        ->and($retrieved->ttl)->toBe(60);

    $driver->delete('test-cache-key');

    expect($driver->get('test-cache-key'))->toBeNull();
});

it('stores and clears cache through CacheHandler', function () {
    freezeTime();

    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_cache_handler';
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);
    $handler = new CacheHandler(
        pendingQuery: $pendingQuery,
        ttl: 120,
        cacheKey: 'custom-handler-key',
        driver: config('cache.default'),
    );

    $handler->set(new CachedResponse(new RecordedResponse(['ok' => true]), 120));

    expect($handler->get())->toBeInstanceOf(CachedResponse::class)
        ->and($handler->get()->recordedResponse->data())->toBe(['ok' => true]);

    $handler->clear();

    expect($handler->get())->toBeNull();
});

it('tracks expiration on CachedResponse', function () {
    freezeTime();

    $cachedResponse = new CachedResponse(new RecordedResponse(['id' => 1]), 60);

    expect($cachedResponse->hasExpired())->toBeFalse()
        ->and($cachedResponse->hasNotExpired())->toBeTrue();

    travelTo(now()->addSeconds(61));

    expect($cachedResponse->hasExpired())->toBeTrue()
        ->and($cachedResponse->hasNotExpired())->toBeFalse();
});

it('builds a fake response from CachedResponse', function () {
    $cachedResponse = new CachedResponse(new RecordedResponse(['id' => 5], ['count' => 1]), 30);
    $fake = $cachedResponse->getFakeResponse();

    expect($fake->getData())->toBe(['id' => 5])
        ->and($fake->getOutput())->toBe(['count' => 1]);
});

it('caches with integer ttl seconds', function () {
    freezeTime();

    $dataEntity = new #[SingleItemResponse] class extends DataEntity implements Cacheable
    {
        use HasCache;

        public function resolveStoreProcedure(): string
        {
            return 'sp_ttl_int';
        }

        public function cacheExpiresAt(): int|DateTimeInterface
        {
            return 60;
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make(['id' => 1]),
    ]);

    expect($dataEntity->execute()->isCached())->toBeFalse()
        ->and($dataEntity->execute()->isCached())->toBeTrue();

    travelTo(now()->addSeconds(61));

    expect($dataEntity->execute()->isCached())->toBeFalse();
});

it('can invalidate cache on next execution', function () {
    freezeTime();

    $dataEntity = new #[SingleItemResponse] class extends DataEntity implements Cacheable
    {
        use HasCache;

        public function resolveStoreProcedure(): string
        {
            return 'sp_invalidate';
        }

        public function cacheExpiresAt(): int|DateTimeInterface
        {
            return now()->addMinute();
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make(['id' => 1]),
    ]);

    $dataEntity->execute();

    expect($dataEntity->execute()->isCached())->toBeTrue();

    $dataEntity->invalidateCache();

    expect($dataEntity->execute()->isCached())->toBeFalse();
});
