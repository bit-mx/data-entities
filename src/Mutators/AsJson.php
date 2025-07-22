<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Mutators;

use BitMx\DataEntities\Contracts\Mutable;
use Illuminate\Support\Collection;

class AsJson implements Mutable
{
    /**
     * @var array<array-key, mixed>
     */
    protected readonly array $attributes;

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
        if (! is_array($value) && ! is_object($value)) {
            throw new \InvalidArgumentException("The value of the parameter {$key} must be an array or object");
        }

        /** @var Collection<int, string> $attributesCollection */
        $attributesCollection = collect($this->attributes);

        $flags = $attributesCollection
            ->filter(fn (string $item): bool => str($item)->startsWith('JSON_'))
            ->map(fn (string $item) => constant($item))
            ->reduce(function (int $carry, int $item): int {
                return $carry | $item;
            }, 0);

        $json = json_encode($value, $flags);

        if ($json === false) {
            throw new \InvalidArgumentException("The value of the parameter {$key} could not be converted to JSON");
        }

        return $json;
    }
}
