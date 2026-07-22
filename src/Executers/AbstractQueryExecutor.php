<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Executers;

use BitMx\DataEntities\Exceptions\InvalidLazyQueryException;
use BitMx\DataEntities\Executers\Contracts\QueryExecutorContract;
use BitMx\DataEntities\PendingQuery;

abstract class AbstractQueryExecutor implements QueryExecutorContract
{
    public function compileQuery(PendingQuery $pendingQuery): string
    {
        $statements = $pendingQuery->statements()->toCollection();

        if ($statements->count() > 1) {
            throw new InvalidLazyQueryException(
                'Multiple statements are not supported in a single query execution. '.
                'Please use a single statement or separate them into multiple queries.'
            );
        }

        $procedure = (string) $statements->first();

        return $this->build($pendingQuery, $procedure);
    }

    abstract protected function build(PendingQuery $pendingQuery, string $procedure): string;

    protected function appendOutputParametersStatements(PendingQuery $pendingQuery): string
    {
        if ($pendingQuery->outputParameters()->isEmpty()) {
            return '';
        }

        $outputParameters = $pendingQuery->outputParameters()->toCollection();

        return $outputParameters->map(function (string $value, string $key) {
            return sprintf('SELECT @%s AS %s;', $key, $key);
        })->implode("\n");
    }
}
