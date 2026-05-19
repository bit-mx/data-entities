<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\PendingQuery;

use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Stores\ParameterStore;

/**
 * @mixin PendingQuery
 */
trait HasAccessorsStore
{
    protected ParameterStore $accessors;

    public function accessors(): ParameterStore
    {
        return $this->accessors ??= new ParameterStore;
    }
}
