<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Mutators;

use BitMx\DataEntities\Contracts\Mutable;

class AsString implements Mutable
{
    /**
     * {@inheritDoc}
     */
    public function transform(string $key, mixed $value, array $parameters): string
    {
        if (! is_scalar($value) && ! is_null($value)) {
            throw new \InvalidArgumentException("The value of the parameter {$key} must be a scalar value");
        }

        return strval($value);
    }
}
