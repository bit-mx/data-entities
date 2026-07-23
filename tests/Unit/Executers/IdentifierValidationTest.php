<?php

declare(strict_types=1);

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Exceptions\InvalidIdentifierException;
use BitMx\DataEntities\Executers\MySqlQueryExecutor;
use BitMx\DataEntities\Executers\SqlServerQueryExecutor;
use BitMx\DataEntities\PendingQuery;

it('accepts qualified procedure names and typed output parameters', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'dbo.sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                'post_id' => 1,
            ];
        }

        protected function defaultOutputParameters(): array
        {
            return [
                'total' => 'INT',
                'amount' => 'DECIMAL(10,2)',
                'title' => 'NVARCHAR(100)',
            ];
        }
    };

    $query = (new SqlServerQueryExecutor)->compileQuery(new PendingQuery($dataEntity));

    expect($query)
        ->toContain('EXEC dbo.sp_test')
        ->toContain('DECLARE @amount DECIMAL(10,2);')
        ->toContain('DECLARE @title NVARCHAR(100);');
});

it('rejects invalid parameter names', function (string $name) {
    $dataEntity = new class($name) extends DataEntity
    {
        public function __construct(private string $parameterName) {}

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                $this->parameterName => 1,
            ];
        }
    };

    (new SqlServerQueryExecutor)->compileQuery(new PendingQuery($dataEntity));
})->with([
    'injection' => 'id; DROP TABLE users--',
    'spaces' => 'post id',
    'empty' => '',
])->throws(InvalidIdentifierException::class);

it('rejects invalid sql types', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultOutputParameters(): array
        {
            return [
                'total' => 'INT; DROP TABLE users--',
            ];
        }
    };

    (new MySqlQueryExecutor)->compileQuery(new PendingQuery($dataEntity));
})->throws(InvalidIdentifierException::class);

it('rejects invalid procedure names', function (string $procedure) {
    $dataEntity = new class($procedure) extends DataEntity
    {
        public function __construct(private string $procedure) {}

        public function resolveStoreProcedure(): string
        {
            return $this->procedure;
        }
    };

    (new SqlServerQueryExecutor)->compileQuery(new PendingQuery($dataEntity));
})->with([
    'injection' => 'sp_test; DROP TABLE users--',
    'spaces' => 'sp test',
])->throws(InvalidIdentifierException::class);
