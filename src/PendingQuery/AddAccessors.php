<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class AddAccessors
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $pendingQuery->accessors()->merge($pendingQuery->getDataEntity()->getAccessors());

        return $pendingQuery;
    }
}
