<?php

namespace BitMx\DataEntities\Contracts;

interface Cacheable
{
    public function cacheExpiresAt(): int|\DateTimeInterface;
}
