<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Executers;

use BitMx\DataEntities\Exceptions\InvalidIdentifierException;
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

        $this->validateIdentifiers($pendingQuery, $procedure);

        return $this->build($pendingQuery, $procedure);
    }

    abstract protected function build(PendingQuery $pendingQuery, string $procedure): string;

    protected function validateIdentifiers(PendingQuery $pendingQuery, string $procedure): void
    {
        $this->assertValidProcedureName($procedure);

        foreach ($pendingQuery->parameters()->keys() as $key) {
            $this->assertValidParameterName((string) $key);
        }

        foreach ($pendingQuery->outputParameters()->all() as $key => $type) {
            $this->assertValidParameterName((string) $key);
            $this->assertValidSqlType((string) $type);
        }
    }

    protected function assertValidProcedureName(string $procedure): void
    {
        if ($procedure === '' || preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)*$/', $procedure) !== 1) {
            throw new InvalidIdentifierException(
                sprintf('Invalid stored procedure name [%s].', $procedure)
            );
        }
    }

    protected function assertValidParameterName(string $name): void
    {
        if ($name === '' || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
            throw new InvalidIdentifierException(
                sprintf('Invalid parameter name [%s].', $name)
            );
        }
    }

    protected function assertValidSqlType(string $type): void
    {
        if ($type === '' || preg_match('/^[A-Za-z][A-Za-z0-9_]*(\s*\(\s*\d+(\s*,\s*\d+)?\s*\))?$/i', $type) !== 1) {
            throw new InvalidIdentifierException(
                sprintf('Invalid SQL type [%s].', $type)
            );
        }
    }

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
