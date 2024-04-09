<?php

namespace BitMx\DataEntities\Factories;

use Faker\Generator;
use Illuminate\Support\Arr;

abstract class DataEntityFactory
{
    protected Generator $faker;

    /**
     * @param  array<array-key, mixed>  $attributes
     * @param  array<array-key, mixed>  $without
     */
    public function __construct(
        protected readonly array $attributes = [],
        public readonly array $without = [],
    ) {
        $this->faker = app(Generator::class);
    }

    /**
     * @param  array<array-key, mixed>  $attributes
     */
    public static function new(
        array $attributes = [],
    ): static {
        return (new static())->state($attributes)->newInstance();
    }

    /**
     * @param  array<array-key, mixed>  $attributes
     * @param  array<array-key, mixed>  $without
     */
    protected function newInstance(
        array $attributes = [],
        array $without = [],
    ): static {
        return new static(
            attributes: array_replace_recursive(
                $this->attributes,
                $attributes,
            ),
            without: array_merge(
                $this->without,
                $without,
            ),
        );
    }

    /**
     * @param  array<array-key, mixed>  $attributes
     */
    public function state(array $attributes): static
    {
        return $this->newInstance(
            attributes: $attributes,
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getData(): array
    {
        return array_merge(
            $this->definition(),
            $this->attributes
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    abstract public function definition(): array;

    /**
     * @param  array<array-key, mixed>|string  $attributes
     */
    public function without(array|string $attributes): static
    {
        return $this->newInstance(
            without: Arr::wrap($attributes),
        );
    }

    /**
     * @param  array<array-key, mixed>  $attributes
     * @return array<array-key, mixed>
     */
    public function create(array $attributes = []): array
    {
        return (new CreateFactoryData)($this->state($attributes));
    }
}
