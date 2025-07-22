<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Plugins;

use BitMx\DataEntities\Cache\CacheHandler;
use BitMx\DataEntities\Cache\CacheMiddleware;
use BitMx\DataEntities\Contracts\Cacheable;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Exceptions\NoCacheableDataEntityException;
use BitMx\DataEntities\PendingQuery;

/**
 * @mixin DataEntity
 */
trait HasCache
{
    protected bool $cachingEnabled = true;

    protected bool $invalidateCache = false;

    public function bootHasCache(PendingQuery $pendingQuery): void
    {
        $dataEntity = $pendingQuery->getDataEntity();

        if (! $dataEntity instanceof Cacheable) {
            throw new NoCacheableDataEntityException(
                sprintf('The data entity %s must implement the %s interface', $dataEntity::class, Cacheable::class),
            );
        }

        if (! $this->cachingEnabled) {
            return;
        }

        $pendingQuery->middleware()->onQuery(function (PendingQuery $middlewarePendingQuery) {
            return call_user_func(
                new CacheMiddleware(
                    ttl: $this->getCacheExpiresInSeconds($middlewarePendingQuery),
                    cacheKey: $this->cacheKey($middlewarePendingQuery),
                    driver: $this->cacheDriver(),
                    invalidate: $this->invalidateCache,
                ),
                $middlewarePendingQuery
            );
        });
    }

    protected function getCacheExpiresInSeconds(PendingQuery $pendingQuery): int
    {
        $dataEntity = $pendingQuery->getDataEntity();

        if (! $dataEntity instanceof Cacheable) {
            return 0;
        }

        $expires = $dataEntity->cacheExpiresAt();

        if ($expires instanceof \DateTimeInterface) {
            return (int) floor(now()->diffInSeconds($expires));
        }

        return $expires;
    }

    protected function cacheKey(PendingQuery $pendingQuery): ?string
    {
        return null;
    }

    protected function cacheDriver(): string
    {
        return config('cache.default');
    }

    public function invalidateCache(): void
    {
        $this->invalidateCache = true;
    }

    public function disableCaching(): void
    {
        $this->cachingEnabled = false;
    }

    public function clearCache(): void
    {
        $pendingQuery = $this->createPendingQuery();

        $cacheHandler = new CacheHandler(
            pendingQuery: $pendingQuery,
            ttl: $this->getCacheExpiresInSeconds($pendingQuery),
            cacheKey: $this->cacheKey($pendingQuery),
            driver: $this->cacheDriver(),
        );

        $cacheHandler->clear();
    }
}
