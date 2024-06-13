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
        protected readonly array $without = [],
        protected readonly int $times = 1,
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
        int $times = 1,
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
            times: $times,
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

    public function count(int $times): static
    {
        return $this->newInstance(
            times: $times,
        );
    }

    public function getData(): FactoryData
    {
        return new FactoryData(
            $this->definition(),
            $this->attributes,
            $this->without,
            $this->output()
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    abstract public function definition(): array;

    /**
     * @return array<array-key, mixed>
     */
    public function output(): array
    {
        return [];
    }

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
        if ($this->times === 1) {
            return (new CreateFactoryData)($this->state($attributes));
        }

        return collect()
            ->times($this->times, function () use ($attributes) {
                return (new CreateFactoryData)($this->state($attributes));
            })
            ->all();
    }
}
