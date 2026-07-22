<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Plugins;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;
use Carbon\CarbonInterval;

/**
 * @mixin DataEntity
 *
 * @phpstan-require-extends DataEntity
 */
trait HasRetries
{
    protected bool $isRetrying = false;

    public function bootHasRetries(PendingQuery $pendingQuery): void
    {
        $pendingQuery->middleware()->onResponse(function (Response $response) {
            return $this->retryResponse($response);
        });
    }

    protected function retryResponse(Response $response): Response
    {
        if ($this->isRetrying || $response->success() || ! $this->shouldRetry($response)) {
            return $response;
        }

        $this->isRetrying = true;

        try {
            $attempts = $this->maxRetryAttempts();
            $delayMs = $this->resolveRetryBackoffMs();
            $latest = $response;

            for ($attempt = 1; $attempt <= $attempts; $attempt++) {
                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }

                $latest = $this->execute();

                if ($latest->success() || ! $this->shouldRetry($latest)) {
                    return $latest;
                }
            }

            return $latest;
        } finally {
            $this->isRetrying = false;
        }
    }

    protected function shouldRetry(Response $response): bool
    {
        $error = $response->getError();

        if ($error === null || $error === '') {
            return false;
        }

        foreach ($this->retryableErrorCodes() as $code) {
            if (str_contains($error, (string) $code)) {
                return true;
            }
        }

        return false;
    }

    protected function maxRetryAttempts(): int
    {
        return 2;
    }

    /**
     * Delay between retry attempts. Prefer a CarbonInterval for readability
     * (e.g. `CarbonInterval::milliseconds(50)` or `CarbonInterval::seconds(1)`).
     * An integer is treated as milliseconds.
     */
    protected function retryBackoff(): int|CarbonInterval
    {
        return 0;
    }

    protected function resolveRetryBackoffMs(): int
    {
        $backoff = $this->retryBackoff();

        if ($backoff instanceof CarbonInterval) {
            return (int) max(0, $backoff->totalMilliseconds);
        }

        return max(0, $backoff);
    }

    /**
     * @return list<int|string>
     */
    protected function retryableErrorCodes(): array
    {
        return [
            1205, // SQL Server deadlock
            1213, // MySQL deadlock
            40001, // Serialization failure
            'HYT00', // Timeout
        ];
    }
}
