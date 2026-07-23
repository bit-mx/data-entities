<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;

class AsObject implements Accessable
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function get(string $key, mixed $value, array $data): ?object
    {
        if (is_null($value)) {
            return null;
        }

        if (is_object($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $decoded = json_decode($value);

        return is_object($decoded) ? $decoded : null;
    }
}
