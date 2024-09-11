<?php

namespace BitMx\DataEntities\Traits\Response;

class FakeResponse
{
    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<array-key, mixed>  $output
     */
    public function __construct(
        protected readonly array $data = [],
        protected readonly array $output = [],
        protected readonly bool $success = true,
    ) {}

    /**
     * @return array<array-key, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }
}
