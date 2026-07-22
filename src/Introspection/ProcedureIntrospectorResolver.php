<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Introspection;

use BitMx\DataEntities\Exceptions\UnsupportedQueryExecutorException;
use BitMx\DataEntities\Introspection\Contracts\ProcedureIntrospectorContract;
use Illuminate\Support\Facades\DB;

class ProcedureIntrospectorResolver
{
    public function resolve(string $connectionName): ProcedureIntrospectorContract
    {
        $driver = config("database.connections.{$connectionName}.driver")
            ?? DB::connection($connectionName)->getDriverName();

        return match ($driver) {
            'sqlsrv' => new SqlServerProcedureIntrospector($connectionName),
            'mysql' => new MySqlProcedureIntrospector($connectionName),
            default => throw new UnsupportedQueryExecutorException(
                sprintf('No procedure introspector registered for driver [%s].', $driver)
            ),
        };
    }
}
