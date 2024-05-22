<?php

namespace BitMx\DataEntities\Casts;

use BitMx\DataEntities\Contracts\Castable;

class AsString implements Castable
{
    /**
     * {@inheritDoc}
     */
    public function transform(string $key, mixed $value, array $parameters): string
    {
        return strval($value);
    }
}
