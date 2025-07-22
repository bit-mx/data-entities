<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;

class AsArray implements Accessable
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function get(string $key, mixed $value, array $data): ?array
    {
        if (is_null($value)) {
            return null;
        }

        return json_decode($value, true);
    }
}
