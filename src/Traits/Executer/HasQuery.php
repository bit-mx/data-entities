<?php

namespace BitMx\DataEntities\Traits\Executer;

use Illuminate\Support\Collection;

/**
 * @property-read  \BitMx\DataEntities\PendingQuery $pendingQuery
 */
trait HasQuery
{
    protected function prepareQuery(): string
    {
        $storeProcedure = $this->pendingQuery->getDataEntity()->resolveStoreProcedure();

        $keys = $this->getParametersKeys();

        $exec = sprintf('EXEC %s ', $storeProcedure);

        $exec .= $keys->map(fn (string $key) => sprintf('@%s = :%s', $key, $key))->implode(', ');

        $exec .= ';';

        return $exec;
    }

    /**
     * @return Collection<int, array-key>
     */
    protected function getParametersKeys(): Collection
    {
        return $this
            ->getParametersCollection()
            ->keys();
    }

    /**
     * @return Collection<array-key, mixed>
     */
    protected function getParametersCollection(): Collection
    {
        return collect($this->pendingQuery->parameters()->all());
    }
}
