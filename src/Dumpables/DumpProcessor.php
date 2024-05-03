<?php

namespace BitMx\DataEntities\Dumpables;

use BitMx\DataEntities\Parameters\ParametersProcessor;
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

        $parameters = (new ParametersProcessor())->process($this->pendingQuery->parameters());

        dd($query, $parameters);
    }
}
