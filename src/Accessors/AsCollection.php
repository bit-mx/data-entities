<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;
use Illuminate\Support\Collection;

class AsCollection implements Accessable
{
    /**
     * @param  array<string, mixed>  $data
     * @return Collection<array-key, mixed>|null
     */
    public function get(string $key, mixed $value, array $data): ?Collection
    {
        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return collect($value);
        }

        if (! is_string($value)) {
            return null;
        }

        $json = json_decode($value, true);

        return collect(is_array($json) ? $json : []);
    }
}
