<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Stores;

use BitMx\DataEntities\Contracts\DataStore;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @implements \ArrayAccess<array-key, mixed>
 */
class ArrayStore implements \ArrayAccess, \Countable, DataStore
{
    /**
     * @var array<array-key, mixed>
     */
    protected array $items = [];

    /**
     * @param  array<array-key, mixed>  $value
     */
    public function __construct(array $value = [])
    {
        $this->set($value);
    }

    /**
     * @param  array<array-key, mixed>  $items
     */
    #[\Override]
    public function set(array $items): self
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param  ?array-key  $key
     */
    #[\Override]
    public function get(string|int|null $key, mixed $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }

    #[\Override]
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * @param  string|int|array<array-key, mixed>|null  $key
     */
    public function add(string|int|array|null $key = null, mixed $value = null): self
    {
        if (is_array($key)) {
            $this->items = [...$this->items, ...$key];

            return $this;
        }

        isset($key)
            ? $this->items[$key] = $value
            : $this->items[] = $value;

        return $this;
    }

    /**
     * @param  array<array-key, mixed>  ...$arrays
     */
    public function merge(array ...$arrays): static
    {
        $this->items = array_merge($this->items, ...$arrays);

        return $this;
    }

    public function prepend(mixed $value, int|string|null $key = null): DataStore
    {
        Arr::prepend($this->items, $value, $key);

        return $this;
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function toCollection(): Collection
    {
        return Collection::make($this->items);
    }

    public function toObject(): object
    {
        return (object) $this->items;
    }

    public function offsetExists(mixed $key): bool
    {
        return isset($this->items[$key]);
    }

    public function offsetGet(mixed $key): mixed
    {
        return $this->items[$key];
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    public function offsetUnset(mixed $key): void
    {
        unset($this->items[$key]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function toArray(): array
    {
        return $this->all();
    }

    /**
     * @return array<array-key, mixed>
     */
    #[\Override]
    public function all(): array
    {
        return $this->items;
    }
}
