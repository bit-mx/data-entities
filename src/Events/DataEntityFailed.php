<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Events;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;
use Throwable;

class DataEntityFailed
{
    public function __construct(
        public readonly DataEntity $dataEntity,
        public readonly PendingQuery $pendingQuery,
        public readonly Response $response,
        public readonly string $query,
        public readonly float $durationMs,
        public readonly Throwable $exception,
    ) {}
}
