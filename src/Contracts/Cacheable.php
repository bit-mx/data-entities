<?php

namespace BitMx\DataEntities\Contracts;

interface Cacheable
{
    public function cacheExpiresInSeconds(): int|\DateTimeInterface;
}
