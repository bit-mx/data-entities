<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;
use Carbon\CarbonImmutable;

class AsDateImmutable implements Accessable
{
    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $value, array $data): ?\DateTimeImmutable
    {
        if (is_null($value)) {
            return null;
        }

        return CarbonImmutable::parse($value);
    }
}
