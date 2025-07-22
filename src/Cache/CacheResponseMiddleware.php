<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Cache;

use BitMx\DataEntities\Contracts\CacheStore;
use BitMx\DataEntities\Contracts\ResponseMiddleware;
use BitMx\DataEntities\Responses\RecordedResponse;
use BitMx\DataEntities\Responses\Response;

class CacheResponseMiddleware implements ResponseMiddleware
{
    protected readonly CacheStore $cache;

    public function __construct(
        protected readonly ?string $cacheKey,
        protected readonly int $cacheTtl = 3600,
        protected readonly string $cacheDriver = 'default'
    ) {
        $this->cache = new CacheDriver($this->cacheDriver);
    }

    public function __invoke(Response $response): Response
    {
        if ($response->failed()) {
            return $response;
        }

        $cacheHandler = new CacheHandler(
            pendingQuery: $response->getPendingQuery(),
            ttl: $this->cacheTtl,
            cacheKey: $this->cacheKey,
            driver: $this->cacheDriver
        );

        $cacheHandler->set(
            new CachedResponse(
                RecordedResponse::fromResponse($response),
                $this->cacheTtl
            )
        );

        return $response;
    }
}
