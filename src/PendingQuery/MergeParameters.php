<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class MergeParameters
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $parameters = $dataEntity->parameters()->all();

        $pendingQuery->parameters()->merge($parameters);

        return $pendingQuery;
    }
}
