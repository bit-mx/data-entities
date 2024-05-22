<?php

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Collection;

beforeEach(function () {
    $this->dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function defaultParameters(): array
        {
            return [
                'test' => 'test',
            ];
        }
    };
});

it('you can get the pending query', function () {
    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['test' => 'test']),
    ]);

    $response = $this->dataEntity->execute();

    $pendingQuery = $response->getPendingQuery();

    expect($pendingQuery)->toBeInstanceOf(PendingQuery::class)
        ->and($pendingQuery->getDataEntity())->toBe($this->dataEntity);
});

it('can get the data as array', function () {
    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['test' => 'test']),
    ]);

    $response = $this->dataEntity->execute();

    expect($response->data())->toBe(['test' => 'test']);
});

it('can get the data by a key', function () {
    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['key' => 'test']),
    ]);

    $response = $this->dataEntity->execute();

    expect($response->data('key'))->toBe('test');
});

it('can get the data as an object', function () {
    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['test' => 'test']),
    ]);

    $response = $this->dataEntity->execute();

    expect($response->object())->toBeObject()
        ->and($response->object()->test)->toBe('test');
});

it('can get the data as collection', function () {
    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['key1' => 'test', 'key2' => 'test']),
    ]);

    $response = $this->dataEntity->execute();

    expect($response->collect())->toBeInstanceOf(Collection::class)
        ->and($response->collect()->count())->toBe(2);
});

it('can get the data by a key with default', function () {
    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['key' => 'test']),
    ]);

    $response = $this->dataEntity->execute();

    expect($response->data('key2', 'key_default'))->toBe('key_default');
});

it('can get isEmpty is data is empty', function () {
    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make([]),
    ]);

    $response = $this->dataEntity->execute();

    expect($response->isEmpty())->toBeTrue()
        ->and($response->isNotEmpty())->toBeFalse();
});

it('can get isEmpty to false is data is not empty', function () {
    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make(['key' => 'test']),
    ]);

    $response = $this->dataEntity->execute();

    expect($response->isEmpty())->toBeFalse()
        ->and($response->isNotEmpty())->toBeTrue();
});

it('returns a empty array if there is no data', function () {
    DataEntity::fake([
        $this->dataEntity::class => MockResponse::make([]),
    ]);

    $response = $this->dataEntity->execute();

    expect($response->data())->toBe([]);
});

@it('returns a DTO', function () {

    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function defaultParameters(): array
        {
            return [
                'test' => 'test',
            ];
        }

        public function createDtoFromResponse(Response $response): mixed
        {
            return (object) $response->data();
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make(['test' => 'test']),
    ]);

    $response = $dataEntity->execute();

    $dto = $response->dto();

    expect($dto->test)->toBe('test');
});
