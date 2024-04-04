<?php

namespace BitMx\DataEntities\Contracts;

interface DataStore
{
    /**
     * @param  array<array-key, mixed>  $value
     */
    public function set(array $value): self;

    public function get(string $key): mixed;

    /**
     * @return array<array-key, mixed>
     */
    public function all(): array;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;

    public function add(): self;

    /**
     * @param  array<array-key, mixed>  ...$arrays
     */
    public function merge(array ...$arrays): self;

    public function prepend(mixed $value, string|int|null $key = null): self;
}
