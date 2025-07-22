<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Mutators;

use BitMx\DataEntities\Contracts\Mutable;

class AsDateTimeFormated implements Mutable
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
    public function transform(string $key, mixed $value, array $parameters): string
    {
        if (! $value instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException("The value of the parameter {$key} must be a DateTimeInterface instance");
        }

        $format = $this->attributes[0] ?? 'Y-m-d H:i:s';

        return $value->format($format);
    }
}
