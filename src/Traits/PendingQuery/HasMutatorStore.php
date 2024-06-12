<?php

namespace BitMx\DataEntities\Traits\PendingQuery;

use BitMx\DataEntities\Stores\ParameterStore;

/**
 * @mixin \BitMx\DataEntities\PendingQuery
 */
trait HasMutatorStore
{
    protected ParameterStore $mutators;

    public function mutators(): ParameterStore
    {
        return $this->mutators ??= new ParameterStore();
    }
}
