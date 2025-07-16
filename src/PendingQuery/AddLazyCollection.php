<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class AddLazyCollection
{
    /**
     * @param  \Closure(PendingQuery): PendingQuery  $next
     */
    public function __invoke(PendingQuery $pendingQuery, \Closure $next): PendingQuery
    {
        if ($pendingQuery->getDataEntity()->useLazyCollection()) {
            $pendingQuery->enableUseLazyCollection();
        }

        return $next($pendingQuery);
    }
}
