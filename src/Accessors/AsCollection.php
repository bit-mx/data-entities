<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;
use Illuminate\Support\Collection;

class AsCollection implements Accessable
{
    /**
     * @param  array<string, mixed>  $data
     * @return Collection<string, mixed>|null
     */
    public function get(string $key, mixed $value, array $data): ?Collection
    {
        if (is_null($value)) {
            return null;
        }

        /** @var array<array-key, mixed> $json */
        $json = json_decode($value, true);

        return collect($json);
    }
}
