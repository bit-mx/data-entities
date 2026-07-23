<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;
use Carbon\Carbon;
use DateTime;
use DateTimeInterface;

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

        if (! is_string($value) && ! is_int($value) && ! is_float($value) && ! $value instanceof DateTimeInterface) {
            return null;
        }

        return Carbon::parse($value);
    }
}
