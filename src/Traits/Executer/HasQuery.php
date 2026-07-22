<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\Executer;

use BitMx\DataEntities\Executers\QueryExecutorResolver;
use BitMx\DataEntities\PendingQuery;

/**
 * @property-read  PendingQuery $pendingQuery
 */
trait HasQuery
{
    protected function prepareQuery(): string
    {
        return (new QueryExecutorResolver)
            ->resolve($this->pendingQuery)
            ->compileQuery($this->pendingQuery);
    }
}
