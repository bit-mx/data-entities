<?php

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Dumpables\DumpProcessor;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Exceptions\UnsupportedQueryExecutorException;
use BitMx\DataEntities\Executers\MySqlQueryExecutor;
use BitMx\DataEntities\Executers\QueryExecutorResolver;
use BitMx\DataEntities\Executers\SqlServerQueryExecutor;
use BitMx\DataEntities\PendingQuery;

it('resolves the sql server executor from the connection driver', function () {
    config()->set('data-entities.database', 'sqlsrv');
    config()->set('database.connections.sqlsrv.driver', 'sqlsrv');

    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $executor = (new QueryExecutorResolver)->resolve(new PendingQuery($dataEntity));

    expect($executor)->toBeInstanceOf(SqlServerQueryExecutor::class);
});

it('resolves the mysql executor from the connection driver', function () {
    config()->set('data-entities.database', 'mysql');
    config()->set('database.connections.mysql.driver', 'mysql');

    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $executor = (new QueryExecutorResolver)->resolve(new PendingQuery($dataEntity));

    expect($executor)->toBeInstanceOf(MySqlQueryExecutor::class);
});

it('allows an entity to override the query executor', function () {
    config()->set('data-entities.database', 'sqlsrv');
    config()->set('database.connections.sqlsrv.driver', 'sqlsrv');

    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function resolveQueryExecutor(): ?string
        {
            return MySqlQueryExecutor::class;
        }
    };

    $executor = (new QueryExecutorResolver)->resolve(new PendingQuery($dataEntity));

    expect($executor)->toBeInstanceOf(MySqlQueryExecutor::class);
});

it('uses the executer mapped in config for a driver', function () {
    config()->set('data-entities.database', 'sqlsrv');
    config()->set('database.connections.sqlsrv.driver', 'sqlsrv');
    config()->set('data-entities.executers.sqlsrv', MySqlQueryExecutor::class);

    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $executor = (new QueryExecutorResolver)->resolve(new PendingQuery($dataEntity));

    expect($executor)->toBeInstanceOf(MySqlQueryExecutor::class);
});

it('throws when no executor is registered for the driver', function () {
    config()->set('data-entities.database', 'pgsql');
    config()->set('database.connections.pgsql.driver', 'pgsql');
    config()->set('data-entities.executers', [
        'sqlsrv' => SqlServerQueryExecutor::class,
        'mysql' => MySqlQueryExecutor::class,
    ]);

    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    (new QueryExecutorResolver)->resolve(new PendingQuery($dataEntity));
})->throws(UnsupportedQueryExecutorException::class, 'No query executor registered for driver [pgsql].');

it('compiles the query through prepareQuery with the executor resolved from the driver', function () {
    config()->set('data-entities.database', 'mysql');
    config()->set('database.connections.mysql.driver', 'mysql');

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
    };

    $processor = new DumpProcessor(new PendingQuery($dataEntity));

    $prepareQuery = new ReflectionMethod($processor, 'prepareQuery');

    expect($prepareQuery->invoke($processor))->toBe('CALL sp_test(:post_id);');
});

it('throws when the resolved executor does not implement the contract', function () {
    config()->set('data-entities.database', 'sqlsrv');
    config()->set('database.connections.sqlsrv.driver', 'sqlsrv');

    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function resolveQueryExecutor(): ?string
        {
            return stdClass::class;
        }
    };

    (new QueryExecutorResolver)->resolve(new PendingQuery($dataEntity));
})->throws(UnsupportedQueryExecutorException::class);
