<?php

namespace BitMx\DataEntities\Dumpables;

use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Traits\Executer\HasQuery;

class DumpProcessor
{
    use HasQuery;

    public function __construct(
        protected readonly PendingQuery $pendingQuery,
    ) {
    }

    public function handler(): never
    {
        $query = $this->prepareQuery();

        dd($query, $this->pendingQuery->parameters()->all());
    }
}
