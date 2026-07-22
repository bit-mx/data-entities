<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use Closure;
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
    public static function transaction(Closure $callback, ?string $connection = null): mixed
    {
        $connectionName = $connection ?? (string) config('data-entities.database', 'sqlsrv');

        return DB::connection($connectionName)->transaction($callback);
    }
}
