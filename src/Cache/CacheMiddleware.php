<?php

namespace BitMx\DataEntities\Cache;

use BitMx\DataEntities\Contracts\CacheStore;
use BitMx\DataEntities\Contracts\QueryMiddleware;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;

class CacheMiddleware implements QueryMiddleware
{
    protected CacheStore $cache;

    public function __construct(
        protected readonly int $ttl,
        protected readonly ?string $cacheKey,
        protected string $driver = 'default',
        protected bool $invalidate = false,
    ) {
        $this->cache = new CacheDriver($this->driver);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $cacheKey = $this->getHashedCacheKey($pendingQuery);

        $cachedResponse = $this->cache->get($cacheKey);

        // if the cache is null, we need to record the response
        if ($cachedResponse instanceof CachedResponse) {
            if (! $this->invalidate) {
                $pendingQuery->middleware()->onResponse(fn (Response $response) => $response->setCached(true));

                $pendingQuery->setFakeResponse($cachedResponse->getFakeResponse());

                return $pendingQuery;
            }

            $this->cache->delete($cacheKey);
        }

        $pendingQuery->middleware()->onResponse(
            callable: new CacheResponseMiddleware(
                cacheKey: $cacheKey,
                cacheExpires: $this->ttl,
                cacheDriver: $this->driver
            )
        );

        return $pendingQuery;
    }

    protected function getHashedCacheKey(PendingQuery $pendingQuery): string
    {
        return hash('sha256', $this->getCacheKey($pendingQuery));
    }

    protected function getCacheKey(PendingQuery $pendingQuery): string
    {
        return $this->cacheKey ?? CacheKey::create($pendingQuery);
    }
}
