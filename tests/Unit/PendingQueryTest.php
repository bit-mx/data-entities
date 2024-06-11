<?php

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
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
