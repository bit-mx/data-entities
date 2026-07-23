<?php

declare(strict_types=1);

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Exceptions\InvalidLazyQueryException;
use BitMx\DataEntities\Executers\MySqlQueryExecutor;
use BitMx\DataEntities\PendingQuery;

it('compiles a procedure call with positional bindings', function () {
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

    $query = (new MySqlQueryExecutor)->compileQuery(new PendingQuery($dataEntity));

    expect($query)->toBe('CALL sp_test(:post_id, :status);');
});

it('compiles output parameters as session variables', function () {
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
                'message' => 'VARCHAR(100)',
            ];
        }
    };

    $query = (new MySqlQueryExecutor)->compileQuery(new PendingQuery($dataEntity));

    expect($query)
        ->toBe('CALL sp_test(:post_id, @total, @message); SELECT @total AS total;'."\n".'SELECT @message AS message;');
});

it('compiles a procedure call without parameters', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $query = (new MySqlQueryExecutor)->compileQuery(new PendingQuery($dataEntity));

    expect($query)->toBe('CALL sp_test();');
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

    $query = (new MySqlQueryExecutor)->compileQuery(new PendingQuery($dataEntity));

    expect($query)->toBe('CALL sp_test(@total); SELECT @total AS total;');
});

it('compiles the procedure call prefix', function () {
    expect((new MySqlQueryExecutor)->compileProcedureCall('sp_users'))
        ->toBe('CALL sp_users');
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

    (new MySqlQueryExecutor)->compileQuery($pendingQuery);
})->throws(InvalidLazyQueryException::class);
