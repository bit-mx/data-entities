<?php

namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;
use Carbon\Carbon;
use DateTime;

class AsDate implements Accessable
{
    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $value, array $data): ?DateTime
    {
        if (is_null($value)) {
            return null;
        }

        return Carbon::parse($value);
    }
}
