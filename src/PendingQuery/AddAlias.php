<?php

declare(strict_types=1);

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class AddAlias
{
    /**
     * @param  \Closure(PendingQuery): PendingQuery  $next
     */
    public function __invoke(PendingQuery $pendingQuery, \Closure $next): PendingQuery
    {
        $pendingQuery->alias()->merge($pendingQuery->getDataEntity()->getalias());

        return $next($pendingQuery);
    }
}
