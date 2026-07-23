<?php

declare(strict_types=1);

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\Contracts\Cacheable;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Plugins\HasCache;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Tests\Helpers\UppercaseAccessor;

use function Pest\Laravel\freezeTime;
use function Pest\Laravel\travelTo;

it('cache response', function () {
    freezeTime();

    $dataEntity = new #[SingleItemResponse] class extends DataEntity implements Cacheable
    {
        use HasCache;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function cacheExpiresAt(): int|DateTimeInterface
        {
            return now()->addMinute();
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make(['id' => 1]),
    ]);

    $response = $dataEntity->execute();

    expect($response->isCached())->toBeFalse();

    $response = $dataEntity->execute();

    expect($response->isCached())->toBeTrue();

    travelTo(now()->addMinutes(2));

    $response = $dataEntity->execute();
    expect($response->isCached())->toBeFalse();
});

it('applies accessors only once when serving a cached response', function () {
    freezeTime();

    $dataEntity = new #[SingleItemResponse] class extends DataEntity implements Cacheable
    {
        use HasCache;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function accessors(): array
        {
            return [
                'title' => UppercaseAccessor::class,
            ];
        }

        public function cacheExpiresAt(): int|DateTimeInterface
        {
            return now()->addMinute();
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make(['title' => 'hello']),
    ]);

    $first = $dataEntity->execute();
    $second = $dataEntity->execute();

    expect($first->data('title'))->toBe('HELLO')
        ->and($second->isCached())->toBeTrue()
        ->and($second->data('title'))->toBe('HELLO')
        ->and($second->rawData('title'))->toBe('hello');
});

it('clears cache', function () {
    freezeTime();

    $dataEntity = new #[SingleItemResponse] class extends DataEntity implements Cacheable
    {
        use HasCache;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function cacheExpiresAt(): int|DateTimeInterface
        {
            return now()->addMinute();
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make(['id' => 1]),
    ]);

    $response = $dataEntity->execute();

    expect($response->isCached())->toBeFalse();

    $response = $dataEntity->execute();

    expect($response->isCached())->toBeTrue();

    $dataEntity->clearCache();
    $response = $dataEntity->execute();
    expect($response->isCached())->toBeFalse();
});
