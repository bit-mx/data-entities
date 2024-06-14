<?php

namespace BitMx\DataEntities\Contracts;

interface DataStore
{
    /**
     * @param  array<array-key, mixed>  $items
     */
    public function set(array $items): self;

    public function get(string $key): mixed;

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;

    /**
     * @param  string|int|array<array-key, mixed>|null  $key
     */
    public function add(string|int|array|null $key = null, mixed $value = null): self;

    /**
     * @param  array<array-key, mixed>  ...$arrays
     */
    public function merge(array ...$arrays): self;

    public function prepend(mixed $value, string|int|null $key = null): self;
}
