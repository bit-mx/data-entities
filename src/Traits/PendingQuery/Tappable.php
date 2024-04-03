<?php

namespace BitMx\DataEntities\Traits\PendingQuery;

trait Tappable
{
    protected function tap(callable $callable): static
    {
        $callable($this);

        return $this;
    }
}
