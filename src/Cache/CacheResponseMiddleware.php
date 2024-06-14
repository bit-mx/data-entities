<?php

namespace BitMx\DataEntities\Cache;

use BitMx\DataEntities\Contracts\CacheStore;
use BitMx\DataEntities\Contracts\ResponseMiddleware;
use BitMx\DataEntities\Responses\RecordedResponse;
use BitMx\DataEntities\Responses\Response;

class CacheResponseMiddleware implements ResponseMiddleware
{
    protected CacheStore $cache;

    public function __construct(
        protected readonly string $cacheKey,
        protected readonly int $cacheExpires = 3600,
        protected readonly string $cacheDriver = 'default'
    ) {
        $this->cache = new CacheDriver($this->cacheDriver);
    }

    public function __invoke(Response $response): Response
    {
        if ($response->failed()) {
            return $response;
        }

        $expiresAt = now()->toImmutable()->addSeconds($this->cacheExpires);

        $this->cache->set(
            key: $this->cacheKey,
            cachedResponse: new CachedResponse(RecordedResponse::fromResponse($response), $expiresAt, $this->cacheExpires)
        );

        return $response;
    }
}
