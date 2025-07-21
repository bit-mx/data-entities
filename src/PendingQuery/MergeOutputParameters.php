<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class MergeOutputParameters
{
    /**
     * @param  \Closure(PendingQuery): PendingQuery  $next
     */
    public function __invoke(PendingQuery $pendingQuery, \Closure $next): PendingQuery
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $outputParameters = $dataEntity->outputParameters()->all();

        $pendingQuery->outputParameters()->merge($outputParameters);

        return $next($pendingQuery);
    }
}
