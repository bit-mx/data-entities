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
    ) {}

    /**
     * {@inheritDoc}
     */
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $cacheHandler = new CacheHandler($pendingQuery, $this->ttl, $this->cacheKey, $this->driver);

        $cachedResponse = $cacheHandler->get();

        // if the cache is null, we need to record the response
        if ($cachedResponse instanceof CachedResponse) {
            if (! $this->invalidate) {
                $pendingQuery->middleware()->onResponse(fn (Response $response) => $response->setCached(true));

                $pendingQuery->setFakeResponse($cachedResponse->getFakeResponse());

                return $pendingQuery;
            }

            $cacheHandler->clear();
        }

        $pendingQuery->middleware()->onResponse(
            callable: new CacheResponseMiddleware(
                cacheKey: $this->cacheKey,
                cacheTtl: $this->ttl,
                cacheDriver: $this->driver
            )
        );

        return $pendingQuery;
    }
}
