<?php

declare(strict_types=1);

use BitMx\DataEntities\DataEntity;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

it('runs the callback inside a database transaction on the given connection', function () {
    $connection = Mockery::mock();
    $connection->shouldReceive('transaction')
        ->once()
        ->with(Mockery::type('callable'))
        ->andReturnUsing(fn (callable $callback) => $callback());

    DB::shouldReceive('connection')
        ->once()
        ->with('legacy_erp')
        ->andReturn($connection);

    $result = DataEntity::transaction(
        fn () => 'ok',
        'legacy_erp',
    );

    expect($result)->toBe('ok');
});

it('defaults to the configured data-entities database connection', function () {
    config()->set('data-entities.database', 'sqlsrv');

    $connection = Mockery::mock();
    $connection->shouldReceive('transaction')
        ->once()
        ->with(Mockery::type('callable'))
        ->andReturnUsing(fn (callable $callback) => $callback());

    DB::shouldReceive('connection')
        ->once()
        ->with('sqlsrv')
        ->andReturn($connection);

    expect(DataEntity::transaction(fn () => 42))->toBe(42);
});

it('accepts a Connection instance for the transaction', function () {
    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('transaction')
        ->once()
        ->with(Mockery::type('callable'))
        ->andReturnUsing(fn (callable $callback) => $callback());

    $result = DataEntity::transaction(
        fn () => 'from-connection',
        $connection,
    );

    expect($result)->toBe('from-connection');
});
