<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Executers;

use BitMx\DataEntities\PendingQuery;

class SqlServerQueryExecutor extends AbstractQueryExecutor
{
    public function compileProcedureCall(string $procedure): string
    {
        return sprintf('EXEC %s ', $procedure);
    }

    protected function build(PendingQuery $pendingQuery, string $procedure): string
    {
        $storeProcedure = $this->compileProcedureCall($procedure);

        $storeProcedure = (string) str(sprintf(
            '%s %s',
            $this->prependOutputParametersStatements($pendingQuery),
            $storeProcedure,
        ))
            ->trim();

        $keys = $pendingQuery->parameters()->keys();

        $exec = $storeProcedure.' ';

        $params = $keys->map(fn (string $key) => sprintf('@%s = :%s', $key, $key));

        $outputParams = $pendingQuery->outputParameters()->keys()
            ->map(fn (string $key) => sprintf('@%s = @%s OUTPUT', $key, $key));

        $exec .= $params->merge($outputParams)->implode(', ');

        $exec .= ';';

        return sprintf(
            '%s %s',
            $exec,
            $this->appendOutputParametersStatements($pendingQuery),
        );
    }

    protected function prependOutputParametersStatements(PendingQuery $pendingQuery): string
    {
        if ($pendingQuery->outputParameters()->isEmpty()) {
            return '';
        }

        $outputParameters = $pendingQuery->outputParameters()->toCollection();

        return $outputParameters->map(function (string $value, string $key) {
            return sprintf('DECLARE @%s %s;', $key, $value);
        })->implode("\n");
    }
}
