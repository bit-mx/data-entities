<?php

namespace BitMx\DataEntities\Responses;

use BitMx\DataEntities\Factories\DataEntityFactory;

final class MockResponse
{
    /**
     * @param  array<array-key, mixed>  $data
     */
    public function __construct(
        protected array|DataEntityFactory|\Throwable $data,
    ) {
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function make(array|DataEntityFactory|\Throwable $data): static
    {
        return new self($data);
    }

    /**
     * @return array<array-key, mixed>|\Throwable
     */
    public function data(): array|\Throwable
    {
        if ($this->data instanceof DataEntityFactory) {
            return $this->data->create();
        }

        return $this->data;
    }
}
