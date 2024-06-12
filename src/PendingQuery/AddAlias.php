<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class AddAlias
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $pendingQuery->setAlias($pendingQuery->getDataEntity()->getalias());

        return $pendingQuery;
    }
}
