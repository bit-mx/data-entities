<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Cache;

use BitMx\DataEntities\PendingQuery;

class CacheKey
{
    public static function create(PendingQuery $pendingQuery): string
    {
        $dataEntity = $pendingQuery->getDataEntity();
        $className = $dataEntity::class;
        $storeProcedure = $dataEntity->resolveStoreProcedure();
        $connection = $dataEntity->resolveDatabaseConnectionIdentity();
        $parameters = $pendingQuery->parameters()->all();
        $outputParameters = $pendingQuery->outputParameters()->all();

        $json = json_encode([
            'className' => $className,
            'storeProcedure' => $storeProcedure,
            'connection' => $connection,
            'parameters' => $parameters,
            'outputParameters' => $outputParameters,
        ]);

        if ($json === false) {
            throw new \RuntimeException('Failed to encode cache key');
        }

        return $json;
    }
}
