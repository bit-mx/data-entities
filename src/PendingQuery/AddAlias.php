<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class AddAlias
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $pendingQuery->alias()->merge($pendingQuery->getDataEntity()->getalias());

        return $pendingQuery;
    }
}
