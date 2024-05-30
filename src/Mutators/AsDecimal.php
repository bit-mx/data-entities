<?php

namespace BitMx\DataEntities\Mutators;

use BitMx\DataEntities\Contracts\Mutable;

class AsDecimal implements Mutable
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
