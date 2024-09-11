<?php

namespace BitMx\DataEntities\Factories;

use BitMx\DataEntities\Enums\ResponseType;
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
        protected ?ResponseType $responseType = null,
    ) {
        $this->faker = app(Generator::class);
    }

    /**
     * @param  array<array-key, mixed>  $attributes
     */
    public static function new(
        array $attributes = [],
    ): static {
        return (new static)->state($attributes)->newInstance();
    }

    /**
     * @param  array<array-key, mixed>  $attributes
     * @param  array<array-key, mixed>  $without
     */
    protected function newInstance(
        array $attributes = [],
        array $without = [],
        ?int $times = null,
        ?ResponseType $responseType = null,
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
            times: $times ?? $this->times,
            responseType: $responseType ?? $this->getResponseType(),
        );
    }

    protected function getResponseType(): ResponseType
    {
        return $this->responseType ?? $this->responseType();
    }

    public function responseType(): ResponseType
    {
        return ResponseType::SINGLE;
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
        $times = $times < 1 ? 1 : $times;

        return $this->newInstance(
            times: $times,
            responseType: $times > 1
                ? ResponseType::COLLECTION
                : null,
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

    public function asCollection(): static
    {
        return $this->setCollectionResponseType();
    }

    protected function setCollectionResponseType(): static
    {
        return $this->setResponseType(ResponseType::COLLECTION);
    }

    protected function setResponseType(ResponseType $responseType): static
    {
        return $this->newInstance(
            responseType: $responseType,
        );
    }

    public function asSingle(): static
    {
        return $this->setSingleResponseType();
    }

    protected function setSingleResponseType(): static
    {
        return $this->setResponseType(ResponseType::SINGLE);
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
            $data = (new CreateFactoryData)($this->state($attributes));

            return $this->getResponseType() === ResponseType::SINGLE ? $data : [$data];
        }

        return collect()
            ->times($this->times, function () use ($attributes) {
                return (new CreateFactoryData)($this->state($attributes));
            })
            ->all();
    }
}
