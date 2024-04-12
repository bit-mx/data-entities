<?php

namespace BitMx\DataEntities\Responses;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Traits\Response\ThrowsError;

readonly class Response
{
    use ThrowsError;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __construct(
        protected PendingQuery $pendingQuery,
        protected array $data = [],
        protected bool $success = true,
        protected ?\Throwable $senderException = null
    ) {
    }

    public function failed(): bool
    {
        return ! $this->success();
    }

    public function success(): bool
    {
        return $this->success;
    }

    public function dto(): mixed
    {
        return $this
            ->pendingQuery
            ->getDataEntity()
            ->createDtoFromResponse(
                $this
            );
    }

    public function getDataEntity(): DataEntity
    {
        return $this->pendingQuery->getDataEntity();
    }

    public function getPendingQuery(): PendingQuery
    {
        return $this->pendingQuery;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }
}
