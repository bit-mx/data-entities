<?php

namespace BitMx\DataEntities\Traits\Executer;

/**
 * @property-read  \BitMx\DataEntities\PendingQuery $pendingQuery
 */
trait HasQuery
{
    protected function prepareQuery(): string
    {
        $storeProcedure = $this->pendingQuery->statements()->toCollection()->join('; ');

        $storeProcedure = (string) str(sprintf(
            '%s %s',
            $this->prependOutputParametersStatements(),
            $storeProcedure,
        ))
            ->trim();

        $keys = $this->pendingQuery->parameters()->keys();

        $exec = $storeProcedure.' ';

        $params = $keys->map(fn (string $key) => sprintf('@%s = :%s', $key, $key));

        $outputParams = $this->pendingQuery->outputParameters()->keys()->map(fn (string $key) => sprintf('@%s = @%s OUTPUT', $key, $key));

        $exec .= $params->merge($outputParams)->implode(', ');

        $exec .= ';';

        $exec = sprintf(
            '%s %s',
            $exec,
            $this->appendOutputParametersStatements(),
        );

        return $exec;
    }

    protected function prependOutputParametersStatements(): string
    {
        if ($this->pendingQuery->outputParameters()->isEmpty()) {
            return '';
        }

        $outputParameters = $this->pendingQuery->outputParameters()->toCollection();

        return $outputParameters->map(function (string $value, string $key) {
            return sprintf('DECLARE @%s %s;', $key, $value);
        })->implode("\n");
    }

    protected function appendOutputParametersStatements(): string
    {
        if ($this->pendingQuery->outputParameters()->isEmpty()) {
            return '';
        }

        $outputParameters = $this->pendingQuery->outputParameters()->toCollection();

        return $outputParameters->map(function (string $value, string $key) {
            return sprintf('SELECT @%s AS %s;', $key, $key);
        })->implode("\n");
    }
}
