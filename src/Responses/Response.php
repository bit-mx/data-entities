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

    /**
     * @var array<array-key, mixed>
     */
    protected readonly array $data;

    /**
     * @var array<array-key, mixed>
     */
    protected readonly array $output;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __construct(
        protected readonly PendingQuery $pendingQuery,
        array $data = [],
        protected readonly bool $success = true,
        protected readonly ?\Throwable $senderException = null
    ) {
        $this->data = $this->getData($data);

        $this->output = $this->getOutput($data);
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected function getData(array $data): array
    {
        if ($this->pendingQuery->outputParameters()->isEmpty()) {
            return $data;
        }

        return $data[0];
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
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected function getOutput(array $data): array
    {
        if ($this->pendingQuery->outputParameters()->isEmpty()) {
            return [];
        }

        return collect($data)
            ->filter(fn (array $value, int $key): bool => $key > 0)
            ->flatMap(fn (array $value): array => $value[0])
            ->all();
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
}
