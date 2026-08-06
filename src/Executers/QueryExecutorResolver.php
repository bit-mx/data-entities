<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Executers;

use BitMx\DataEntities\Exceptions\UnsupportedQueryExecutorException;
use BitMx\DataEntities\Executers\Contracts\QueryExecutorContract;
use BitMx\DataEntities\PendingQuery;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class QueryExecutorResolver
{
    public function resolve(PendingQuery $pendingQuery): QueryExecutorContract
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $executorClass = $dataEntity->resolveQueryExecutor()
            ?? $this->resolveFromDriver($dataEntity->resolveEffectiveDatabaseConnection());

        return $this->make($executorClass);
    }

    protected function make(string $executorClass): QueryExecutorContract
    {
        if (! class_exists($executorClass) || ! is_a($executorClass, QueryExecutorContract::class, true)) {
            throw new UnsupportedQueryExecutorException(
                sprintf('The query executor [%s] must implement %s.', $executorClass, QueryExecutorContract::class)
            );
        }

        return new $executorClass;
    }

    /**
     * @return class-string<QueryExecutorContract>
     */
    protected function resolveFromDriver(string|Connection $connection): string
    {
        $driver = $this->resolveDriverName($connection);

        /** @var array<string, class-string<QueryExecutorContract>> $executers */
        $executers = config('data-entities.executers', []);

        if (! isset($executers[$driver])) {
            throw new UnsupportedQueryExecutorException(
                sprintf('No query executor registered for driver [%s].', $driver)
            );
        }

        return $executers[$driver];
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
