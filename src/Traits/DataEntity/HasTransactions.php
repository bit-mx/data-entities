<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

trait HasTransactions
{
    /**
     * Run multiple Data Entity executions inside a single database transaction.
     *
     * All entities inside the callback must use the same connection (pass it
     * explicitly, or rely on `config('data-entities.database')`).
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public static function transaction(Closure $callback, string|Connection|null $connection = null): mixed
    {
        if ($connection instanceof Connection) {
            return $connection->transaction($callback);
        }

        if ($connection !== null) {
            $connectionName = $connection;
        } else {
            $configured = config('data-entities.database', 'sqlsrv');
            $connectionName = is_string($configured) && $configured !== '' ? $configured : 'sqlsrv';
        }

        return DB::connection($connectionName)->transaction($callback);
    }
}
