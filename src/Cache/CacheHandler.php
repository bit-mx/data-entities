<?php

namespace BitMx\DataEntities\Cache;

use BitMx\DataEntities\Contracts\CacheStore;
use BitMx\DataEntities\PendingQuery;

class CacheHandler
{
    protected CacheStore $cache;

    public function __construct(
        protected readonly PendingQuery $pendingQuery,
        protected readonly int $ttl,
        protected readonly ?string $cacheKey,
        protected string $driver = 'default',
    ) {
        $this->cache = new CacheDriver($this->driver);
    }

    public function get(): ?CachedResponse
    {
        return $this->cache->get($this->getCacheKey());
    }

    protected function getCacheKey(): string
    {
        return $this->cacheKey ?? $this->getHashedCacheKey();
    }

    protected function getHashedCacheKey(): string
    {
        return hash('sha256', CacheKey::create($this->pendingQuery));
    }

    public function clear(): void
    {
        $this->cache->delete($this->getCacheKey());
    }

    public function set(CachedResponse $cachedResponse): void
    {
        $this->cache->set(
            key: $this->getCacheKey(),
            cachedResponse: $cachedResponse,
        );
    }
}
