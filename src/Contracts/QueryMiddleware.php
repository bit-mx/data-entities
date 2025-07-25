<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Contracts;

use BitMx\DataEntities\PendingQuery;

interface QueryMiddleware
{
    /**
     * @return PendingQuery|void
     */
    public function __invoke(PendingQuery $pendingQuery): mixed;
}
