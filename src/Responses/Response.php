<?php

namespace BitMx\DataEntities\Responses;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Stores\ArrayStore;
use BitMx\DataEntities\Traits\Response\HasMutatedData;
use BitMx\DataEntities\Traits\Response\ThrowsError;

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

    public function data(): ArrayStore
    {
        return $this->data;
    }

    public function output(): ArrayStore
    {
        return $this->output;
    }

    public function rawData(): ArrayStore
    {
        return $this->rawData;
    }

    public function rawOutput(): ArrayStore
    {
        return $this->rawOutput;
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
}
