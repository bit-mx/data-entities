<?php

namespace BitMx\DataEntities\Responses;

use BitMx\DataEntities\Factories\DataEntityFactory;

final class MockResponse
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __construct(
        protected array|DataEntityFactory $data,
    ) {
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function make(array|DataEntityFactory $data): static
    {
        return new self($data);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function data(): array
    {
        if ($this->data instanceof DataEntityFactory) {
            return $this->data->create();
        }

        return $this->data;
    }
}
