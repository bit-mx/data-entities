<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class AddMutators
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $pendingQuery->setMutators($pendingQuery->getDataEntity()->getMutators());

        return $pendingQuery;
    }
}
