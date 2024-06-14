<?php

namespace BitMx\DataEntities\Responses;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Traits\Response\HasMutatedData;
use BitMx\DataEntities\Traits\Response\ThrowsError;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Response
{
    use HasMutatedData;
    use ThrowsError;

    protected bool $cached = false;

    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<array-key, mixed>  $output
     */
    public function __construct(
        protected readonly PendingQuery $pendingQuery,
        protected readonly array $data = [],
        protected readonly array $output = [],
        protected readonly bool $success = true,
        protected readonly ?\Throwable $senderException = null
    ) {
    }

    /**
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function rawData(?string $key = null): mixed
    {
        if (is_null($key)) {
            return $this->data;
        }

        return Arr::get($this->data, $key);
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
        $data = $this->mutatedData();

        if (is_null($key)) {
            return $data;
        }

        return Arr::get($data, $key, $default);
    }

    /**
     * @param  ?array-key  $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function output(string|int|null $key = null, mixed $default = null): mixed
    {
        $data = $this->mutatedOutput();

        if (is_null($key)) {
            return $data;
        }

        return Arr::get($data, $key, $default);
    }

    /**
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function rawOutput(?string $key = null): mixed
    {
        if (is_null($key)) {
            return $this->output;
        }

        return Arr::get($this->output, $key);
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

    public function isCached(): bool
    {
        return $this->cached;
    }

    public function setCached(bool $cached = true): self
    {
        $this->cached = $cached;

        return $this;
    }
}
