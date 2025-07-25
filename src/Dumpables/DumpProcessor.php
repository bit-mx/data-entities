<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Dumpables;

use BitMx\DataEntities\Parameters\ParametersProcessor;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Traits\Executer\HasQuery;
use Symfony\Component\VarDumper\VarDumper;

class DumpProcessor
{
    use HasQuery;

    public function __construct(
        protected readonly PendingQuery $pendingQuery,
    ) {}

    public function handler(): never
    {
        $query = $this->prepareQuery();

        $parameters = (new ParametersProcessor($this->pendingQuery))->process();

        VarDumper::dump([
            $query,
            $parameters,
        ]);

        exit(1);
    }
}
