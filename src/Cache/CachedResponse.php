<?php

namespace BitMx\DataEntities\Cache;

use BitMx\DataEntities\Responses\RecordedResponse;
use BitMx\DataEntities\Traits\Response\FakeResponse;
use DateTimeImmutable;

class CachedResponse
{
    public function __construct(
        public readonly RecordedResponse $recordedResponse,
        public readonly ?DateTimeImmutable $expiresAt,
        public readonly int $ttl,
    ) {
    }

    public function hasNotExpired(): bool
    {
        return ! $this->hasExpired();
    }

    public function hasExpired(): bool
    {
        return $this->expiresAt->getTimestamp() <= (new DateTimeImmutable)->getTimestamp();
    }

    /**
     * Create a fake response
     */
    public function getFakeResponse(): FakeResponse
    {
        $response = $this->recordedResponse;

        return new FakeResponse(
            data: $response->data(),
            output: $response->output(),
            success: true,
        );
    }
}
