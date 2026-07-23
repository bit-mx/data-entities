<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;

class AsArray implements Accessable
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<array-key, mixed>|null
     */
    public function get(string $key, mixed $value, array $data): ?array
    {
        if (is_null($value)) {
            return null;
        }

        if (! is_string($value)) {
            return is_array($value) ? $value : null;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : null;
    }
}
