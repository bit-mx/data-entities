<?php

use BitMx\DataEntities\Attributes\UseLazyQuery;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Exceptions\InvalidLazyQueryException;
use BitMx\DataEntities\PendingQuery;

it('merges the raw store procedure name into statements', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    expect($pendingQuery->statements()->all())->toBe(['sp_test']);
});

it('merges entity parameters into the pending query', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                'post_id' => 10,
                'status' => 'active',
            ];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    expect($pendingQuery->parameters()->all())->toBe([
        'post_id' => 10,
        'status' => 'active',
    ]);
});

it('merges entity output parameters into the pending query', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultOutputParameters(): array
        {
            return [
                'total' => 'INT',
                'message' => 'NVARCHAR(100)',
            ];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    expect($pendingQuery->outputParameters()->all())->toBe([
        'total' => 'INT',
        'message' => 'NVARCHAR(100)',
    ]);
});

it('merges mutators into the pending query', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function mutators(): array
        {
            return [
                'amount' => 'decimal:2',
                'active' => 'bool',
            ];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    expect($pendingQuery->mutators()->all())->toBe([
        'amount' => 'decimal:2',
        'active' => 'bool',
    ]);
});

it('merges accessors into the pending query', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function accessors(): array
        {
            return [
                'created_at' => 'datetime',
                'price' => 'decimal',
            ];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    expect($pendingQuery->accessors()->all())->toBe([
        'created_at' => 'datetime',
        'price' => 'decimal',
    ]);
});

it('merges aliases into the pending query', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function alias(): array
        {
            return [
                'post_id' => 'id',
                'post_title' => 'title',
            ];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    expect($pendingQuery->alias()->all())->toBe([
        'post_id' => 'id',
        'post_title' => 'title',
    ]);
});

it('enables lazy collection when UseLazyQuery is present', function () {
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

it('throws when UseLazyQuery is combined with output parameters', function () {
    $dataEntity = new #[UseLazyQuery] class extends DataEntity
    {
        protected ?ResponseType $responseType = ResponseType::COLLECTION;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultOutputParameters(): array
        {
            return [
                'total' => 'INT',
            ];
        }
    };

    new PendingQuery($dataEntity);
})->throws(
    InvalidLazyQueryException::class,
    'Lazy collection cannot be used with output parameters'
);
