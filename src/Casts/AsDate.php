<?php

namespace BitMx\DataEntities\Casts;

use BitMx\DataEntities\Contracts\Castable;

class AsDate implements Castable
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
    public function transform(string $key, mixed $value, array $parameters): mixed
    {
        if (! $value instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException("The value of the parameter {$key} must be a DateTimeInterface instance");
        }

        $format = $this->attributes[0] ?? 'Y-m-d';

        return $value->format($format);
    }
}
