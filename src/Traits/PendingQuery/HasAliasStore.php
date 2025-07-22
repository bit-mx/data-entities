<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\PendingQuery;

use BitMx\DataEntities\Stores\ParameterStore;

trait HasAliasStore
{
    protected ParameterStore $alias;

    public function alias(): ParameterStore
    {
        return $this->alias ??= new ParameterStore;
    }
}
