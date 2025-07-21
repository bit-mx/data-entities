<?php

use BitMx\DataEntities\Attributes\UseLazyQuery;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Tests\Helpers\StringEnum;
use BitMx\DataEntities\Tests\Helpers\UppercaseAccessor;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

beforeEach(function () {
    $this->dataEntity = new class extends DataEntity {
        protected ?ResponseType $responseType = ResponseType::SINGLE;

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

    $dataEntity = new class extends DataEntity {
        protected ?ResponseType $responseType = ResponseType::SINGLE;

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

it('mutate response data', function () {
    $dataEntity = new class extends DataEntity {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

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

        protected function accessors(): array
        {
            return [
                'value_int' => 'integer',
                'value_int2' => 'int',
                'value_decimal' => 'decimal',
                'value_string' => 'string',
                'value_bool' => 'boolean',
                'value_date' => 'date',
                'value_enum' => StringEnum::class,
                'value_array' => 'array',
                'value_object' => 'object',
                'value_collection' => 'collection',
                'value_date_immutable' => 'date_immutable',
            ];
        }

        public function createDtoFromResponse(Response $response): mixed
        {
            return (object) $response->data();
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make([
            'value_int' => '1',
            'value_int2' => '2',
            'value_decimal' => '1.1',
            'value_string' => 1,
            'value_bool' => 1,
            'value_date' => '2021-01-01',
            'value_date_immutable' => '2021-01-01',
            'value_enum' => StringEnum::PAID->value,
            'value_array' => '["test", "test2"]',
            'value_object' => '{"test": "test"}',
            'value_collection' => '[{"test": "test"}, {"test": "test2"}]',
            'value_no_mutated' => '1',
        ]),
    ]);

    $response = $dataEntity->execute();

    expect($response->data('value_int'))->toBeInt()->toBe(1)
        ->and($response->data('value_int2'))->toBeInt()->toBe(2)
        ->and($response->data('value_decimal'))->toBeFloat()
        ->and($response->data('value_decimal'))->toBe(1.1)
        ->and($response->data('value_string'))->toBeString()
        ->and($response->data('value_string'))->toBe('1')
        ->and($response->data('value_bool'))->toBeBool()
        ->and($response->data('value_bool'))->toBeTrue()
        ->and($response->data('value_date'))->toBeInstanceOf(DateTime::class)
        ->and($response->data('value_date_immutable'))->toBeInstanceOf(DateTimeImmutable::class)
        ->and($response->data('value_enum'))->toBeInstanceOf(StringEnum::class)
        ->and($response->data('value_enum'))->toBe(StringEnum::PAID)
        ->and($response->data('value_array'))->toBeArray()
        ->and($response->data('value_array'))->toBe(['test', 'test2'])
        ->and($response->data('value_object'))->toBeObject()
        ->and($response->data('value_object')->test)->toBe('test')
        ->and($response->data('value_collection'))->toBeInstanceOf(Collection::class)
        ->and($response->data('value_collection')->count())->toBe(2)
        ->and($response->data('value_no_mutated'))->toBe('1');

});

it('mutate response collection data', function () {
    $dataEntity = new class extends DataEntity {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::COLLECTION;

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

        protected function accessors(): array
        {
            return [
                'value_int' => 'integer',
                'value_int2' => 'int',
                'value_decimal' => 'decimal',
                'value_float' => 'float',
                'value_string' => 'string',
                'value_bool' => 'boolean',
                'value_date' => 'date',
                'value_enum' => StringEnum::class,
                'value_array' => 'array',
                'value_object' => 'object',
                'value_collection' => 'collection',
                'value_date_immutable' => 'date_immutable',
            ];
        }

        public function createDtoFromResponse(Response $response): mixed
        {
            return (object) $response->data();
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make([
            [
                'value_int' => '1',
                'value_int2' => '2',
                'value_decimal' => '1.1',
                'value_float' => '2.2',
                'value_string' => 1,
                'value_bool' => 1,
                'value_date' => '2021-01-01',
                'value_date_immutable' => '2021-01-01',
                'value_enum' => StringEnum::PAID->value,
                'value_array' => '["test", "test2"]',
                'value_object' => '{"test": "test"}',
                'value_collection' => '[{"test": "test"}, {"test": "test2"}]',
                'value_no_mutated' => '1',
            ],
            [
                'value_int' => '1',
                'value_int2' => '2',
                'value_decimal' => '1.1',
                'value_float' => '2.2',
                'value_string' => 1,
                'value_bool' => 1,
                'value_date' => '2021-01-01',
                'value_date_immutable' => '2021-01-01',
                'value_enum' => StringEnum::PAID->value,
                'value_array' => '["test", "test2"]',
                'value_object' => '{"test": "test"}',
                'value_collection' => '[{"test": "test"}, {"test": "test2"}]',
                'value_no_mutated' => '1',
            ],
        ]),
    ]);

    $response = $dataEntity->execute();

    $data = $response->data();

    expect($data[0]['value_int'])->toBeInt()->toBe(1)
        ->and($data[0]['value_int2'])->toBeInt()->toBe(2)
        ->and($data[0]['value_decimal'])->toBeFloat()->toBe(1.1)
        ->and($data[0]['value_float'])->toBeFloat()->toBe(2.2)
        ->and($data[0]['value_string'])->toBeString()->toBe('1')
        ->and($data[0]['value_bool'])->toBeBool()->toBeTrue()
        ->and($data[0]['value_date'])->toBeInstanceOf(DateTime::class)
        ->and($data[0]['value_date_immutable'])->toBeInstanceOf(DateTimeImmutable::class)
        ->and($data[0]['value_enum'])->toBeInstanceOf(StringEnum::class)->toBe(StringEnum::PAID)
        ->and($data[0]['value_array'])->toBeArray()->toBe(['test', 'test2'])
        ->and($data[0]['value_object'])->toBeObject()
        ->and($data[0]['value_object']->test)->toBe('test')
        ->and($data[0]['value_collection'])->toBeInstanceOf(Collection::class)
        ->and($data[0]['value_collection']->count())->toBe(2)
        ->and($data[0]['value_no_mutated'])->toBe('1');

});

it('get value with a custom Accessor', function () {

    $dataEntity = new class extends DataEntity {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::COLLECTION;

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

        protected function accessors(): array
        {
            return [
                'value' => UppercaseAccessor::class,
            ];
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::makeWithException(new Exception('error')),
    ]);

    $response = $dataEntity->execute();

    expect($response->data())->toBe([]);

    $response->throw();

})
    ->throws(Exception::class, 'error');

it('returns a lazy collection if UseLAzyQuery attribute es defined', function () {
    $dataEntity = new #[UseLazyQuery] class extends DataEntity {
        protected ?ResponseType $responseType = ResponseType::COLLECTION;

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

    DataEntity::fake([
        $dataEntity::class => MockResponse::make([['test' => 'test']]),
    ]);

    $response = $dataEntity->execute();

    expect($response->data())->toBeEmpty()
        ->and($response->lazy())->toBeInstanceOf(LazyCollection::class)
        ->and($response->lazy()->first())->toBe(['test' => 'test']);
});

it('returns a lazy collection casted if UseLAzyQuery attribute es defined', function () {
    $dataEntity = new #[UseLazyQuery] class extends DataEntity {
        protected ?ResponseType $responseType = ResponseType::COLLECTION;

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

        public function accessors(): array
        {
            return [
                'id' => 'integer',
            ];
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make([
            [
                'test' => 'test',
                'id' => '2',
            ],
        ]),
    ]);

    $response = $dataEntity->execute();

    expect($response->data())->toBeEmpty()
        ->and($response->lazy()->first()['id'] === 2)->toBeTrue();
});
