<?php

namespace BitMx\DataEntities\Traits\Response;

use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Mutators\AccessorProcessor;
use BitMx\DataEntities\Responses\Response;

/**
 * @mixin Response
 */
trait HasMutatedData
{
    /**
     * @var array<array-key, mixed>
     */
    protected array $mutatedData;

    /**
     * @var array<array-key, mixed>
     */
    protected array $aliasData;

    /**
     * @var array<array-key, mixed>
     */
    protected array $aliasOutput;

    /**
     * @var array<array-key, mixed>
     */
    protected array $mutatedOutput;

    /**
     * @return array<array-key, mixed>
     */
    protected function mutatedData(): array
    {
        return $this->mutatedData ??= $this->getMutatedData();
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getMutatedData(): array
    {
        if ($this->getDataEntity()->getResponseType() === ResponseType::SINGLE) {
            return $this->mutateSingleData($this->aliasData());
        }

        return $this->mutateCollectionData($this->aliasData());
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected function mutateSingleData(array $data): array
    {
        return $this->mutateResponse($data);
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected function mutateResponse(array $data): array
    {
        if ($this->pendingQuery->accessors()->isEmpty()) {
            return $data;
        }

        return AccessorProcessor::make($data, $this->pendingQuery)->process();
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function aliasData(): array
    {
        return $this->aliasData ??= $this->getAliasData();

    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getAliasData(): array
    {
        return $this->getDataEntity()->getResponseType() === ResponseType::SINGLE
            ? $this->aliasSingleData()
            : $this->aliasCollectionData();
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function aliasSingleData(): array
    {
        return $this->aliasResponse($this->rawData());
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected function aliasResponse(array $data = []): array
    {
        if ($this->pendingQuery->alias()->isEmpty()) {
            return $data;
        }

        if (empty($data)) {
            return [];
        }

        return collect($data)
            ->mapWithKeys(fn (mixed $value, string $key): array => [$this->getParameterAlias($key) => $value])
            ->all();
    }

    protected function getParameterAlias(string $parameter): string
    {
        return $this->pendingQuery->alias()->get($parameter, $parameter);
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function aliasCollectionData(): array
    {
        return collect($this->rawData())
            ->map(fn (array $value): array => $this->aliasResponse($value))
            ->all();
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    protected function mutateCollectionData(array $data): array
    {
        return collect($data)
            ->map(fn (mixed $value): array => $this->mutateSingleData($value))
            ->all();
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function mutatedOutput(): array
    {
        return $this->mutatedOutput ??= $this->mutateResponse($this->aliasOutput());
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function aliasOutput(): array
    {
        return $this->aliasOutput ??= $this->aliasResponse($this->rawOutput());
    }
}
