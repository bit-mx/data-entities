<?php

namespace BitMx\DataEntities\Casts;

use BitMx\DataEntities\Contracts\Castable;

class AsInteger implements Castable
{
    /**
     * {@inheritDoc}
     */
    public function transform(string $key, mixed $value, array $parameters): int
    {
        return intval($value);
    }
}
