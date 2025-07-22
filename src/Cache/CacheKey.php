<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Cache;

use BitMx\DataEntities\PendingQuery;

class CacheKey
{
    public static function create(PendingQuery $pendingQuery): string
    {
        $className = $pendingQuery->getDataEntity()::class;
        $storeProcedure = $pendingQuery->getDataEntity()->resolveStoreProcedure();
        $parameters = $pendingQuery->parameters()->all();
        $outputParameters = $pendingQuery->outputParameters()->all();

        $json = json_encode([
            'className' => $className,
            'storeProcedure' => $storeProcedure,
            'parameters' => $parameters,
            'outputParameters' => $outputParameters,
        ]);

        if ($json === false) {
            throw new \RuntimeException('Failed to encode cache key');
        }

        return $json;
    }
}
