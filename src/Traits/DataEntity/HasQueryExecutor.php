<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\Executers\Contracts\QueryExecutorContract;

trait HasQueryExecutor
{
    /**
     * @return class-string<QueryExecutorContract>|null
     */
    public function resolveQueryExecutor(): ?string
    {
        return null;
    }
}
