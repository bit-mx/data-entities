<?php

namespace BitMx\DataEntities\Plugins;

use BitMx\DataEntities\DataEntity;

/**
 * @mixin DataEntity
 */
trait UseLazyCollection
{
    public function bootUseLazyCollection(): void
    {
        $this->enableUseLazyCollection();
    }
}
