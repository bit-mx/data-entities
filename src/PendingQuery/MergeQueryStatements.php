<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class MergeQueryStatements
{
    /**
     * @param  \Closure(PendingQuery): PendingQuery  $next
     */
    public function __invoke(PendingQuery $pendingQuery, \Closure $next): PendingQuery
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $storeProcedure = sprintf('EXEC %s ', $dataEntity->resolveStoreProcedure());

        $statements = [];

        $currentStatements = $dataEntity->statements()->all();

        $pendingQuery->statements()
            ->merge($statements, $currentStatements, [$storeProcedure]);

        return $next($pendingQuery);
    }
}
