<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Cache;

use BitMx\DataEntities\Contracts\CacheStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

readonly class CacheDriver implements CacheStore
{
    public function __construct(
        protected string $driver,
    ) {}

    public function set(string $key, CachedResponse $cachedResponse): void
    {
        $this->driver()->put($key, serialize($cachedResponse), $cachedResponse->ttl);
    }

    protected function driver(): Repository
    {
        return Cache::driver($this->driver);
    }

    public function delete(string $key): void
    {
        $this->driver()->delete($key);
    }

    public function get(string $key): ?CachedResponse
    {
        $data = $this->driver()->get($key);

        if (empty($data)) {
            return null;
        }

        return unserialize($data, ['allowed_classes' => true]);
    }
}
