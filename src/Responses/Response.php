<?php

namespace BitMx\DataEntities\Responses;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Stores\ArrayStore;
use BitMx\DataEntities\Traits\Response\HasMutatedData;
use BitMx\DataEntities\Traits\Response\ThrowsError;
use Illuminate\Support\Collection;

class Response
{
    use HasMutatedData;
    use ThrowsError;

    protected ArrayStore $rawData;

    protected ArrayStore $rawOutput;

    protected bool $cached = false;

    protected ArrayStore $data;

    protected ArrayStore $output;

    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<array-key, mixed>  $output
     */
    public function __construct(
        protected readonly PendingQuery $pendingQuery,
        array $data = [],
        array $output = [],
        protected readonly bool $success = true,
        protected readonly ?\Throwable $senderException = null
    ) {
        $this->rawData = new ArrayStore($data);
        $this->rawOutput = new ArrayStore($output);

        $this->data = new ArrayStore($this->mutatedData());

        $this->output = new ArrayStore($this->mutatedOutput());
    }

    /**
     * @param  ?array-key  $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function data(string|int|null $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->data->all();
        }

        return $this->data->get($key, $default);
    }

    /**
     * @param  ?array-key  $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function output(string|int|null $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->output->all();
        }

        return $this->output->get($key, $default);
    }

    /**
     * @param  ?array-key  $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function rawData(string|int|null $key, mixed $default): mixed
    {
        if ($key === null) {
            return $this->rawData->all();
        }

        return $this->rawData->get($key, $default);
    }

    /**
     * @param  ?array-key  $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function rawOutput(string|int|null $key, mixed $default): mixed
    {
        if ($key === null) {
            return $this->rawOutput->all();
        }

        return $this->rawOutput->get($key, $default);
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
        return $this->data->isEmpty();
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

    /**
     * @param  string|int|array<array-key, mixed>|null  $key
     */
    public function addData(string|int|array|null $key = null, mixed $value = null): void
    {
        $this->data->add($key, $value);
    }

    /**
     * @param  string|int|array<array-key, mixed>|null  $key
     */
    public function addOutput(string|int|array|null $key = null, mixed $value = null): void
    {
        $this->output->add($key, $value);
    }

    /**
     * @param  array<array-key, mixed>  ...$data
     */
    public function mergeData(array ...$data): void
    {
        $this->data->merge($data);
    }

    /**
     * @param  array<array-key, mixed>  ...$data
     */
    public function mergeOutput(array ...$data): void
    {
        $this->output->merge($data);
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function collect(): Collection
    {
        return $this->data->toCollection();
    }

    public function object(): object
    {
        return $this->data->toObject();
    }
}
