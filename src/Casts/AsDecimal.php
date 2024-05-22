<?php

namespace BitMx\DataEntities\Casts;

use BitMx\DataEntities\Contracts\Castable;

class AsDecimal implements Castable
{
    /**
     * {@inheritDoc}
     */
    public function transform(string $key, mixed $value, array $parameters): float
    {
        if (! is_numeric($value)) {
            throw new \InvalidArgumentException("The value of the parameter {$key} must be a number value");
        }

        $decimals = $this->attributes[0] ?? 2;

        return round(floatval($value), $decimals);
    }
}
