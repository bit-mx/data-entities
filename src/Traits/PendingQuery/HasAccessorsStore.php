<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\PendingQuery;

use BitMx\DataEntities\Stores\ParameterStore;

/**
 * @mixin \BitMx\DataEntities\PendingQuery
 */
trait HasAccessorsStore
{
    protected ParameterStore $accessors;

    public function accessors(): ParameterStore
    {
        return $this->accessors ??= new ParameterStore;
    }
}
