<?php

namespace BitMx\DataEntities\Tests\Helpers;

use BitMx\DataEntities\Contracts\Accessable;

class UppercaseAccessor implements Accessable
{
    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $value, array $data): mixed
    {
        return mb_strtoupper($value);
    }
}
