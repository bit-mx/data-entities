<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class MergeMiddlewares
{
    /**
     * @param  \Closure(PendingQuery): PendingQuery  $next
     */
    public function __invoke(PendingQuery $pendingQuery, \Closure $next): PendingQuery
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $middleware = $dataEntity->middleware();

        $pendingQuery->middleware()->merge($middleware);

        return $next($pendingQuery);
    }
}
