<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;

class AsInteger implements Accessable
{
    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $value, array $data): ?int
    {
        if (is_null($value)) {
            return null;
        }

        return intval($value);
    }
}
