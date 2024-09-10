<?php

use BitMx\DataEntities\Contracts\Cacheable;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\MockResponse;

beforeEach(function () {
    $this->dataEntity = new class extends DataEntity implements Cacheable
    {
        use \BitMx\DataEntities\Plugins\HasCache;

        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function cacheExpiresAt(): DateTimeInterface
        {
            return now()->addHour();
        }
    };

});

it('can get cached results', function () {

    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['test' => 1]),
    ]);

    $response1 = $this->dataEntity->execute();
    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['test' => 2]),
    ]);
    $response2 = $this->dataEntity->execute();

    expect($response1->data()["test"])->toBe(1)
        ->and($response2->data()["test"])->toBe(1);
});

it('can delete cache', function () {

    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['test' => 1]),
    ]);

    $response1 = $this->dataEntity->execute();

    $this->dataEntity->clearCache();

    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['test' => 2]),
    ]);

    $response2 = $this->dataEntity->execute();

    expect($response1->data()["test"])->toBe(1)
        ->and($response2->data()["test"])->toBe(2);
});
