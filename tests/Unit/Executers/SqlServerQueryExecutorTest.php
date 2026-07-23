<?php

declare(strict_types=1);

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Exceptions\InvalidLazyQueryException;
use BitMx\DataEntities\Executers\SqlServerQueryExecutor;
use BitMx\DataEntities\PendingQuery;

it('compiles a procedure call with named parameters', function () {
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

    $query = (new SqlServerQueryExecutor)->compileQuery(new PendingQuery($dataEntity));

    expect($query)->toBe('EXEC sp_test @post_id = :post_id, @status = :status; ');
});

it('compiles output parameters with declare and select', function () {
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
            ];
        }

        protected function defaultOutputParameters(): array
        {
            return [
                'total' => 'INT',
                'message' => 'NVARCHAR(100)',
            ];
        }
    };

    $query = (new SqlServerQueryExecutor)->compileQuery(new PendingQuery($dataEntity));

    expect($query)
        ->toContain('DECLARE @total INT;')
        ->toContain('DECLARE @message NVARCHAR(100);')
        ->toContain('EXEC sp_test @post_id = :post_id, @total = @total OUTPUT, @message = @message OUTPUT;')
        ->toContain('SELECT @total AS total;')
        ->toContain('SELECT @message AS message;');
});

it('compiles a procedure call without parameters', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $query = (new SqlServerQueryExecutor)->compileQuery(new PendingQuery($dataEntity));

    expect($query)->toBe('EXEC sp_test ; ');
});

it('compiles output parameters without input parameters', function () {
    $dataEntity = new class extends DataEntity
    {
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

    $query = (new SqlServerQueryExecutor)->compileQuery(new PendingQuery($dataEntity));

    expect($query)
        ->toContain('DECLARE @total INT;')
        ->toContain('EXEC sp_test @total = @total OUTPUT;')
        ->toContain('SELECT @total AS total;');
});

it('compiles the procedure call prefix', function () {
    expect((new SqlServerQueryExecutor)->compileProcedureCall('dbo.sp_users'))
        ->toBe('EXEC dbo.sp_users ');
});

it('throws when multiple statements are present', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);
    $pendingQuery->statements()->add('SELECT 1');

    (new SqlServerQueryExecutor)->compileQuery($pendingQuery);
})->throws(InvalidLazyQueryException::class);
