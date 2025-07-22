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
        return strval($value);
    }
}
