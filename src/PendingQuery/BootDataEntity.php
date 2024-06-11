<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class BootDataEntity
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $pendingQuery->getDataEntity()->boot($pendingQuery);

        return $pendingQuery;
    }
}
