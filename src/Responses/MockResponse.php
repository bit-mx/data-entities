<?php

namespace BitMx\DataEntities\Responses;

final class MockResponse
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __construct(
        protected array $data,
    ) {
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function make(array $data): static
    {
        return new self($data);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }
}
