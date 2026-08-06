<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Introspection;

use BitMx\DataEntities\Exceptions\UnsupportedQueryExecutorException;
use BitMx\DataEntities\Introspection\Contracts\ProcedureIntrospectorContract;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class ProcedureIntrospectorResolver
{
    public function resolve(string|Connection $connection): ProcedureIntrospectorContract
    {
        $driver = $this->resolveDriverName($connection);

        return match ($driver) {
            'sqlsrv' => new SqlServerProcedureIntrospector($connection),
            'mysql' => new MySqlProcedureIntrospector($connection),
            default => throw new UnsupportedQueryExecutorException(
                sprintf('No procedure introspector registered for driver [%s].', $driver)
            ),
        };
    }

    protected function resolveDriverName(string|Connection $connection): string
    {
        if ($connection instanceof Connection) {
            return $connection->getDriverName();
        }

        $driver = config("database.connections.{$connection}.driver");

        if (is_string($driver) && $driver !== '') {
            return $driver;
        }

        return DB::connection($connection)->getDriverName();
    }
}
