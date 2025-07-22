<?php

declare(strict_types=1);

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class AddMutators
{
    /**
     * @param  \Closure(PendingQuery): PendingQuery  $next
     */
    public function __invoke(PendingQuery $pendingQuery, \Closure $next): PendingQuery
    {
        $pendingQuery->mutators()->merge($pendingQuery->getDataEntity()->getMutators());

        return $next($pendingQuery);
    }
}
