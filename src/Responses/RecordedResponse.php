<?php

namespace BitMx\DataEntities\Responses;

use JsonSerializable;

class RecordedResponse implements JsonSerializable
{
    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<array-key, mixed>  $output
     */
    public function __construct(
        public array $data = [],
        public array $output = [],
    ) {
    }

    public static function fromResponse(Response $response): self
    {
        return new self(
            $response->data(),
            $response->output(),
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * @return array<array-key, mixed>
     */
    public function output(): array
    {
        return $this->output;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'data' => $this->data,
            'output' => $this->output,
        ];
    }
}
