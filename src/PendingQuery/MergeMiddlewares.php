<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class MergeMiddlewares
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $middleware = $dataEntity->middleware();

        $pendingQuery->middleware()->merge($middleware);

        return $pendingQuery;
    }
}
