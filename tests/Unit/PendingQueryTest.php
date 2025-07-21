<?php

use BitMx\DataEntities\Attributes\UseLazyQuery;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Exceptions\InvalidLazyQueryException;
use BitMx\DataEntities\PendingQuery;

beforeEach(function () {
    $this->dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };
});

it('creates a PendingQuery', function () {
    $pendingQuery = new PendingQuery($this->dataEntity);

    expect($pendingQuery->getDataEntity())->toBe($this->dataEntity)
        ->and($pendingQuery->getMethod())->toBe(Method::SELECT);
});

it('executes query middlewares', function () {
    $this->dataEntity->middleware()->onQuery(function (PendingQuery $pendingQuery) {
        $pendingQuery->parameters()->add('test', 'test 1');
    });

    $pendingQuery = new PendingQuery($this->dataEntity);

    expect($pendingQuery->parameters()->all())->toBe(['test' => 'test 1']);
});

test('if data entity has UseLazyQuery attribute', function(){

    $dataEntity = new #[UseLazyQuery] class extends DataEntity
    {
        protected ?ResponseType $responseType = ResponseType::COLLECTION;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    expect($pendingQuery->usesLazyCollection())->toBeTrue();
});

it('throws an exception if data entity has UseLazyQuery attribute and response type is SINGLE ', function(){

    $dataEntity = new #[UseLazyQuery] class extends DataEntity
    {
        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    expect($pendingQuery->usesLazyCollection())->not->toBeTrue();
})
->throws(InvalidLazyQueryException::class);
