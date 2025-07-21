<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\Attributes\UseLazyQuery;
use BitMx\DataEntities\PendingQuery;

readonly class AddLazyCollection
{
    /**
     * @param  \Closure(PendingQuery): PendingQuery  $next
     */
    public function __invoke(PendingQuery $pendingQuery, \Closure $next): PendingQuery
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $reflection = new \ReflectionClass($dataEntity);

        $attributes = $reflection->getAttributes(UseLazyQuery::class);

        if (! empty($attributes)) {
            $pendingQuery->enableUseLazyCollection();
        }

        return $next($pendingQuery);
    }
}
