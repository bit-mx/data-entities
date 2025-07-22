<?php

declare(strict_types=1);

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class MergeParameters
{
    public function __invoke(PendingQuery $pendingQuery, \Closure $next): PendingQuery
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $parameters = $dataEntity->parameters()->all();

        $pendingQuery->parameters()->merge($parameters);

        return $next($pendingQuery);
    }
}
