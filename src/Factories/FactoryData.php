<?php

namespace BitMx\DataEntities\Factories;

readonly class FactoryData
{
    /**
     * @param  array<array-key, mixed>  $definition
     * @param  array<array-key, mixed>  $attributes
     * @param  array<array-key, mixed>  $without
     */
    public function __construct(
        private array $definition,
        private array $attributes,
        private array $without,
    ) {
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getData(): array
    {
        return collect($this->getAttributes())
            ->forget($this->getWithout())
            ->all();
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getAttributes(): array
    {
        return array_replace_recursive($this->definition, $this->attributes);
    }

    /**
     * @return array<int, array-key>
     */
    public function getWithout(): array
    {
        return $this->without;
    }
}
