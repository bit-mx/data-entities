<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Contracts;

use BitMx\DataEntities\Cache\CachedResponse;

interface CacheStore
{
    public function set(string $key, CachedResponse $cachedResponse): void;

    public function delete(string $key): void;

    public function get(string $key): ?CachedResponse;
}
