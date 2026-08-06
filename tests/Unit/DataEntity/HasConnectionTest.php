<?php

declare(strict_types=1);

use BitMx\DataEntities\Cache\CacheKey;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Executers\MySqlQueryExecutor;
use BitMx\DataEntities\Executers\QueryExecutorResolver;
use BitMx\DataEntities\Executers\SqlServerQueryExecutor;
use BitMx\DataEntities\PendingQuery;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

it('resolves a string connection name from config by default', function () {
    config()->set('data-entities.database', 'sqlsrv');

    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    expect($dataEntity->resolveDatabaseConnection())->toBe('sqlsrv')
        ->and($dataEntity->resolveDatabaseConnectionIdentity())->toBe('sqlsrv');
});

it('allows resolveDatabaseConnection to return a Connection instance', function () {
    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('getName')->andReturn('tenant_dynamic');
    $connection->shouldReceive('getDriverName')->andReturn('mysql');
    $connection->shouldReceive('getDatabaseName')->andReturn('tenant_db');

    $dataEntity = new class($connection) extends DataEntity
    {
        public function __construct(protected Connection $dynamicConnection) {}

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function resolveDatabaseConnection(): string|Connection
        {
            return $this->dynamicConnection;
        }
    };

    expect($dataEntity->resolveDatabaseConnection())->toBe($connection)
        ->and($dataEntity->resolveConnection())->toBe($connection)
        ->and($dataEntity->resolveDatabaseConnectionIdentity())->toBe('tenant_dynamic');
});

it('prefers onConnection override over resolveDatabaseConnection', function () {
    config()->set('data-entities.database', 'sqlsrv');

    $override = Mockery::mock(Connection::class);
    $override->shouldReceive('getName')->andReturn('runtime_conn');
    $override->shouldReceive('getDriverName')->andReturn('mysql');
    $override->shouldReceive('getDatabaseName')->andReturn('runtime_db');

    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function resolveDatabaseConnection(): string|Connection
        {
            return 'sqlsrv';
        }
    };

    $dataEntity->onConnection($override);

    expect($dataEntity->resolveEffectiveDatabaseConnection())->toBe($override)
        ->and($dataEntity->resolveConnection())->toBe($override)
        ->and($dataEntity->resolveDatabaseConnectionIdentity())->toBe('runtime_conn');
});

it('allows onConnection with a string name', function () {
    config()->set('data-entities.database', 'sqlsrv');

    $resolved = Mockery::mock(Connection::class);

    DB::shouldReceive('connection')
        ->once()
        ->with('tenant_mysql')
        ->andReturn($resolved);

    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $dataEntity->onConnection('tenant_mysql');

    expect($dataEntity->resolveEffectiveDatabaseConnection())->toBe('tenant_mysql')
        ->and($dataEntity->resolveConnection())->toBe($resolved)
        ->and($dataEntity->resolveDatabaseConnectionIdentity())->toBe('tenant_mysql');
});

it('falls back to driver:database when Connection has no name', function () {
    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('getName')->andReturn(null);
    $connection->shouldReceive('getDriverName')->andReturn('sqlsrv');
    $connection->shouldReceive('getDatabaseName')->andReturn('legacy');

    $dataEntity = new class($connection) extends DataEntity
    {
        public function __construct(protected Connection $dynamicConnection) {}

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function resolveDatabaseConnection(): string|Connection
        {
            return $this->dynamicConnection;
        }
    };

    expect($dataEntity->resolveDatabaseConnectionIdentity())->toBe('sqlsrv:legacy');
});

it('changes the cache key when onConnection overrides the connection', function () {
    config()->set('data-entities.database', 'sqlsrv');

    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $withoutOverride = CacheKey::create(new PendingQuery($dataEntity));

    $dataEntity->onConnection('mysql');
    $withOverride = CacheKey::create(new PendingQuery($dataEntity));

    expect($withoutOverride)->not->toBe($withOverride)
        ->and($withOverride)->toContain('"connection":"mysql"');
});

it('resolves the query executor from a Connection driver', function () {
    config()->set('data-entities.executers.mysql', MySqlQueryExecutor::class);

    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('getDriverName')->andReturn('mysql');

    $dataEntity = new class($connection) extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function __construct(protected Connection $dynamicConnection) {}

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function resolveDatabaseConnection(): string|Connection
        {
            return $this->dynamicConnection;
        }
    };

    $executor = (new QueryExecutorResolver)->resolve(new PendingQuery($dataEntity));

    expect($executor)->toBeInstanceOf(MySqlQueryExecutor::class);
});

it('resolves the query executor from onConnection override', function () {
    config()->set('data-entities.database', 'sqlsrv');
    config()->set('database.connections.sqlsrv.driver', 'sqlsrv');
    config()->set('data-entities.executers.sqlsrv', SqlServerQueryExecutor::class);
    config()->set('data-entities.executers.mysql', MySqlQueryExecutor::class);

    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('getDriverName')->andReturn('mysql');

    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function resolveDatabaseConnection(): string|Connection
        {
            return 'sqlsrv';
        }
    };

    $dataEntity->onConnection($connection);

    $executor = (new QueryExecutorResolver)->resolve(new PendingQuery($dataEntity));

    expect($executor)->toBeInstanceOf(MySqlQueryExecutor::class);
});
