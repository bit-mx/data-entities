<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

class AddCasts
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $pendingQuery->setCasts($pendingQuery->getDataEntity()->getCasts());

        return $pendingQuery;
    }
}
