<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\Attributes\UseLazyQuery;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Exceptions\InvalidLazyQueryException;
use BitMx\DataEntities\PendingQuery;

readonly class AddLazyCollection
{
    /**
     * @param  \Closure(PendingQuery): PendingQuery  $next
     */
    public function __invoke(PendingQuery $pendingQuery, \Closure $next): PendingQuery
    {
        if($this->hasUseLazyQueryAttribute($pendingQuery)) {
            $this->enableLazyCollection($pendingQuery);
        }

        return $next($pendingQuery);
    }

    protected function enableLazyCollection(PendingQuery $pendingQuery): void
    {
        if ($pendingQuery->getDataEntity()->getResponseType() === ResponseType::SINGLE) {
            throw new InvalidLazyQueryException(
                'Lazy collection cannot be used with single response type. Please use collection response type instead.'
            );
        }
        $pendingQuery->enableUseLazyCollection();
    }

    protected function hasUseLazyQueryAttribute(PendingQuery $pendingQuery):bool
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $reflection = new \ReflectionClass($dataEntity);

        $attributes = $reflection->getAttributes(UseLazyQuery::class);

        return !empty($attributes);
    }
}
