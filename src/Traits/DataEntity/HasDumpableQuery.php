<?php

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Dumpables\DumpProcessor;
use BitMx\DataEntities\Dumpables\DumpRawProcessor;

/**
 * @mixin DataEntity
 */
trait HasDumpableQuery
{
    public function dd(): never
    {
        $processor = new DumpProcessor($this->createPendingQuery());

        $processor->handler();
    }

    public function ddRaw(): never
    {
        $processor = new DumpRawProcessor($this->createPendingQuery());

        $processor->handler();
    }
}
