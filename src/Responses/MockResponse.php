<?php

namespace BitMx\DataEntities\Responses;

use BitMx\DataEntities\Factories\DataEntityFactory;

final readonly class MockResponse
{
    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<array-key, mixed>  $output
     */
    public function __construct(
        protected array|DataEntityFactory $data,
        protected array $output = [],
        protected ?\Throwable $exception = null
    ) {

    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    public static function make(array|DataEntityFactory $data): self
    {
        return new self($data);
    }

    public static function makeWithException(\Throwable $exception): self
    {
        return new self([], [], $exception);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function data(): array
    {
        if (empty($this->getOutput())) {
            return $this->getData();
        }

        return [
            $this->getData(),
            $this->getOutput(),
        ];
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getOutput(): array
    {

        $output = $this->data instanceof DataEntityFactory
            ? array_replace_recursive($this->data->getData()->getOutput(), $this->output)
            : $this->output;

        if (empty($output)) {
            return [];
        }

        return [$output];
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function getData(): array
    {
        if ($this->data instanceof DataEntityFactory) {
            return $this->data->create();
        }

        return $this->data;
    }

    public function hasException(): bool
    {
        return $this->exception !== null;
    }

    public function exception(): ?\Throwable
    {
        return $this->exception;
    }
}
