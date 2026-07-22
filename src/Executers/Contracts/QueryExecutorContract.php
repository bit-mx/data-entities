<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Executers\Contracts;

use BitMx\DataEntities\PendingQuery;

interface QueryExecutorContract
{
    public function compileQuery(PendingQuery $pendingQuery): string;

    public function compileProcedureCall(string $procedure): string;
}
