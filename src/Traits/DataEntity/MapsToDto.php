<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\Attributes\MapTo;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\Response;
use ReflectionClass;

/**
 * @mixin DataEntity
 */
trait MapsToDto
{
    public function createDtoFromResponse(Response $response): mixed
    {
        $attributes = (new ReflectionClass($this))->getAttributes(MapTo::class);

        if ($attributes === []) {
            return null;
        }

        $mapTo = $attributes[0]->newInstance();

        /** @var class-string $dtoClass */
        $dtoClass = $mapTo->class;
        /** @var array<array-key, mixed> $data */
        $data = $response->data();

        if ($mapTo->collection !== null) {
            return $this->instantiateMappedCollection($dtoClass, $mapTo->collection, $data);
        }

        if ($data !== [] && array_is_list($data)) {
            $data = $data[0] ?? [];
        }

        /** @var array<array-key, mixed> $data */
        return $this->instantiateMappedDto($dtoClass, $data);
    }

    /**
     * @param  class-string  $dtoClass
     * @param  class-string  $collectionClass
     * @param  array<array-key, mixed>  $data
     */
    protected function instantiateMappedCollection(string $dtoClass, string $collectionClass, array $data): mixed
    {
        $rows = $this->rowsForMappedCollection($data);

        $dtos = [];

        foreach ($rows as $row) {
            $dtos[] = $this->instantiateMappedDto($dtoClass, $row);
        }

        return new $collectionClass($dtos);
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @return list<array<array-key, mixed>>
     */
    protected function rowsForMappedCollection(array $data): array
    {
        if ($data === []) {
            return [];
        }

        if (array_is_list($data)) {
            /** @var list<array<array-key, mixed>> $data */
            return $data;
        }

        /** @var array<array-key, mixed> $data */
        return [$data];
    }

    /**
     * @param  class-string  $dtoClass
     * @param  array<array-key, mixed>  $data
     */
    protected function instantiateMappedDto(string $dtoClass, array $data): mixed
    {
        $constructor = (new ReflectionClass($dtoClass))->getConstructor();

        if ($constructor === null) {
            return new $dtoClass;
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $data)) {
                $arguments[$name] = $data[$name];

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[$name] = $parameter->getDefaultValue();

                continue;
            }

            $arguments[$name] = null;
        }

        return new $dtoClass(...$arguments);
    }
}
