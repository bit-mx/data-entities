<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Mutators;

use BitMx\DataEntities\Contracts\Mutable;

class AsInteger implements Mutable
{
    /**
     * {@inheritDoc}
     */
    public function transform(string $key, mixed $value, array $parameters): int
    {
        if (! is_scalar($value) && ! is_array($value) && ! is_null($value)) {
            throw new \InvalidArgumentException("The value of the parameter {$key} must be a scalar or array value");
        }

        return intval($value);
    }
}
