<?php

namespace BitMx\DataEntities\Dumpables;

use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Traits\Executer\HasQuery;
use Illuminate\Support\Str;

class DumpRawProcessor
{
    use HasQuery;

    public function __construct(
        protected readonly PendingQuery $pendingQuery,
    ) {
    }

    public function handler(): never
    {
        $query = $this->prepareQuery();

        $keys = $this->pendingQuery->parameters()->keys()->map(fn (string $key) => sprintf(':%s', $key));

        $query = Str::replace($keys, $this->pendingQuery->parameters()->toCollection()->values(), $query);

        dd($query);
    }
}
