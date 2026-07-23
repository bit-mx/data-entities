<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Mutators;

use BitMx\DataEntities\Contracts\Mutable;

class AsJson implements Mutable
{
    /**
     * @var list<string>
     */
    protected readonly array $attributes;

    public function __construct(string ...$attributes)
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

        $flags = collect($this->attributes)
            ->filter(fn (string $item): bool => str($item)->startsWith('JSON_'))
            ->map(function (string $item): int {
                $constant = constant($item);

                return is_int($constant) ? $constant : 0;
            })
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
