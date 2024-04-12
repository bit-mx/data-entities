<?php

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;

it('creates a data entity', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    expect($dataEntity->resolveStoreProcedure())->toBe('sp_test')
        ->and($dataEntity->getMethod())->toBe(Method::SELECT)
        ->and($dataEntity->getResponseType())->toBe(ResponseType::SINGLE);
});

test('if is not enable fake then DataEntity is not fakeable', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    expect($dataEntity::isFake())->toBeFalse();
});

test('if fake is enabled fake then DataEntity is fakeable', function () {
    DataEntity::fake();

    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    expect($dataEntity::isFake())->toBeTrue();
});
