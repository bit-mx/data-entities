<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Mutators;

use BitMx\DataEntities\Contracts\Mutable;

class AsDecimal implements Mutable
{
    /**
     * @var array<array-key, mixed>
     */
    protected array $attributes;

    /**
     * @param  array<array-key, mixed>  $attributes
     */
    public function __construct(...$attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function transform(string $key, mixed $value, array $parameters): float
    {
        if (! is_numeric($value)) {
            throw new \InvalidArgumentException("The value of the parameter {$key} must be a number value");
        }

        $decimals = (int) ($this->attributes[0] ?? 2);

        return round(floatval($value), $decimals);
    }
}
