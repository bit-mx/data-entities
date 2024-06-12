<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class MergeOutputParameters
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $outputParameters = $dataEntity->outputParameters()->all();

        $pendingQuery->outputParameters()->merge($outputParameters);

        return $pendingQuery;
    }
}
