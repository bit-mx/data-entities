<?php

namespace BitMx\DataEntities\Traits\Executer;

/**
 * @property-read  \BitMx\DataEntities\PendingQuery $pendingQuery
 */
trait HasQuery
{
    protected function prepareQuery(): string
    {
        $storeProcedure = $this->pendingQuery->statements()->toCollection()->join(';');

        $keys = $this->pendingQuery->parameters()->keys();

        $exec = sprintf('EXEC %s ', $storeProcedure);

        $exec .= $keys->map(fn (string $key) => sprintf('@%s = :%s', $key, $key))->implode(', ');

        $exec .= ';';

        return $exec;
    }
}
