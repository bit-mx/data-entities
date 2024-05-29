<?php

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

        return json_decode($value);
    }
}
