<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Executers;

use BitMx\DataEntities\PendingQuery;

class MySqlQueryExecutor extends AbstractQueryExecutor
{
    public function compileProcedureCall(string $procedure): string
    {
        return sprintf('CALL %s', $procedure);
    }

    protected function build(PendingQuery $pendingQuery, string $procedure): string
    {
        $inputParams = $pendingQuery->parameters()->keys()
            ->map(fn (string $key) => sprintf(':%s', $key));

        $outputParams = $pendingQuery->outputParameters()->keys()
            ->map(fn (string $key) => sprintf('@%s', $key));

        $params = $inputParams->merge($outputParams)->implode(', ');

        return sprintf('%s(%s);', $this->compileProcedureCall($procedure), $params);
    }
}
