<?php

namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;

class AsBool implements Accessable
{
    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $value, array $data): ?bool
    {
        if (is_null($value)) {
            return null;
        }

        return boolval($value);
    }
}
