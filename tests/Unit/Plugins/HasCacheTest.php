<?php

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Plugins\HasCache;
use BitMx\DataEntities\Responses\MockResponse;

use function Pest\Laravel\freezeTime;
use function Pest\Laravel\travelTo;

it('cache response', function () {
    freezeTime();

    $dataEntity = new #[SingleItemResponse] class extends DataEntity implements \BitMx\DataEntities\Contracts\Cacheable
    {
        use HasCache;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function cacheExpiresAt(): int|\DateTimeInterface
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

it('clears cache', function () {
    freezeTime();

    $dataEntity = new #[SingleItemResponse] class extends DataEntity implements \BitMx\DataEntities\Contracts\Cacheable
    {
        use HasCache;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function cacheExpiresAt(): int|\DateTimeInterface
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
