<?php

namespace BitMx\DataEntities\Responses;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Mutators\AccessorProcessor;
use BitMx\DataEntities\Traits\Response\ThrowsError;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

readonly class Response
{
    use ThrowsError;

    /**
     * @var array<array-key, mixed>
     */
    protected array $data;

    /**
     * @var array<array-key, mixed>
     */
    protected array $output;

    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __construct(
        protected PendingQuery $pendingQuery,
        array $data = [],
        protected bool $success = true,
        protected ?\Throwable $senderException = null
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
        $outputParametersCount = $this->pendingQuery->outputParameters()->toCollection()->count();

        if ($outputParametersCount === 0) {
            return $data;
        }

        return $data[0];
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected function getOutput(array $data): array
    {
        $outputParametersCount = $this->pendingQuery->outputParameters()->toCollection()->count();

        if ($outputParametersCount === 0) {
            return [];
        }

        return collect($data)
            ->filter(fn (array $value, int $key): bool => $key > 0)
            ->flatMap(fn (array $value): array => $value[0])
            ->mapWithKeys(fn (mixed $value, string $key): array => [$this->getAliasOutputParameter($key) => $value])
            ->all();
    }

    protected function getAliasOutputParameter(string $parameter): string
    {
        if ($this->pendingQuery->outputParameters()->isEmpty()) {
            return $parameter;
        }

        if (empty($this->pendingQuery->getalias())) {
            return $parameter;
        }

        return $this->pendingQuery->getalias()[$parameter] ?? $parameter;
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
        $data = $this->mutatedData($this->rawData());

        if (is_null($key)) {
            return $data;
        }

        return Arr::get($data, $key, $default);
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected function mutatedData(array $data): array
    {
        return AccessorProcessor::make($data, $this->pendingQuery)->process();
    }

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
        $data = $this->mutatedData($this->rawOutput());

        if (is_null($key)) {
            return $data;
        }

        return Arr::get($data, $key, $default);
    }

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

    /**
     * @return array<int, string>
     */
    protected function outputKeys(): array
    {
        return $this->pendingQuery->outputParameters()->toCollection()->keys()->toArray();
    }
}
