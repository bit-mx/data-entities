<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Cache;

use BitMx\DataEntities\Responses\RecordedResponse;
use BitMx\DataEntities\Traits\Response\FakeResponse;
use DateTimeImmutable;

class CachedResponse
{
    protected readonly DateTimeImmutable $expiresAt;

    public function __construct(
        public readonly RecordedResponse $recordedResponse,
        public readonly int $ttl,
    ) {
        $this->expiresAt = now()->toImmutable()->addSeconds($this->ttl);
    }

    public function hasNotExpired(): bool
    {
        return ! $this->hasExpired();
    }

    public function hasExpired(): bool
    {
        return $this->expiresAt->getTimestamp() <= now()->getTimestamp();
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
