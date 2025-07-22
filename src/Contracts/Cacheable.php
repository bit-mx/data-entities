<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Contracts;

interface Cacheable
{
    public function cacheExpiresAt(): int|\DateTimeInterface;
}
