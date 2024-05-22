<?php

namespace BitMx\DataEntities\Responses;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Traits\Response\ThrowsError;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

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

    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    public function isEmpty(): bool
    {
        return empty($this->data());
    }

    /**
     * @param  ?array-key  $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function data(string|int|null $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->data;
        }

        return Arr::get($this->data, $key, $default);
    }

    public function object(): object
    {
        return (object) $this->data();
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function collect(): Collection
    {
        return collect($this->data());
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
}
