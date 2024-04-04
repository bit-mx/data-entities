<?php

namespace BitMx\DataEntities\Stores;

use BitMx\DataEntities\Contracts\DataStore;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ArrayStore implements DataStore
{
    /**
     * @var array<array-key, mixed>
     */
    protected array $data = [];

    /**
     * @param  array<array-key, mixed>  $value
     */
    public function __construct(array $value = [])
    {
        $this->set($value);
    }

    /**
     * @param  array<array-key, mixed>  $value
     */
    #[\Override]
    public function set(array $value): self
    {
        $this->data = $value;

        return $this;
    }

    #[\Override]
    public function get(string $key): mixed
    {
        return Arr::get($this->data, $key);
    }

    /**
     * @return array<array-key, mixed>
     */
    #[\Override]
    public function all(): array
    {
        return $this->data;
    }

    #[\Override]
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    public function add(string|int|null $key = null, mixed $value = null): self
    {
        isset($key)
            ? $this->data[$key] = $value
            : $this->data[] = $value;

        return $this;
    }

    /**
     * @param  array<array-key, mixed>  ...$arrays
     */
    public function merge(array ...$arrays): static
    {
        $this->data = array_merge($this->data, ...$arrays);

        return $this;
    }

    public function prepend(mixed $value, int|string|null $key = null): DataStore
    {
        Arr::prepend($this->data, $value, $key);

        return $this;
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function toCollection(): Collection
    {
        return Collection::make($this->data);
    }
}
