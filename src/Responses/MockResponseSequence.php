<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Responses;

use BitMx\DataEntities\Exceptions\MockResponseNotFoundException;

class MockResponseSequence
{
    private int $index = 0;

    /**
     * @param  list<MockResponse>  $responses
     */
    public function __construct(
        protected array $responses,
    ) {}

    public static function make(MockResponse ...$responses): self
    {
        return new self(array_values($responses));
    }

    public function push(MockResponse $response): self
    {
        $this->responses[] = $response;

        return $this;
    }

    public function next(): MockResponse
    {
        if (! array_key_exists($this->index, $this->responses)) {
            throw new MockResponseNotFoundException('No more mock responses left in the sequence.');
        }

        return $this->responses[$this->index++];
    }
}
