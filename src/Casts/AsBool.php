<?php

namespace BitMx\DataEntities\Casts;

use BitMx\DataEntities\Contracts\Castable;

class AsBool implements Castable
{
    /**
     * {@inheritDoc}
     */
    public function transform(string $key, mixed $value, array $parameters): int
    {
        return (int) boolval($value);
    }
}
