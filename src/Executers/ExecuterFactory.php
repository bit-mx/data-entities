<?php

namespace BitMx\DataEntities\Executers;

use BitMx\DataEntities\Contracts\ExecuterContract;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Exceptions\InvalidMethodException;
use BitMx\DataEntities\PendingQuery;

readonly class ExecuterFactory
{
    public function __construct(
        protected Method $method,
        protected PendingQuery $pendingQuery,
    ) {
    }

    public static function make(Method $method, PendingQuery $pendingQuery): self
    {
        return new self($method, $pendingQuery);
    }

    public function create(): ExecuterContract
    {
        return match ($this->method) {
            Method::SELECT => new SelectExecuter($this->pendingQuery),
            default => throw new InvalidMethodException('Invalid method'),
        };
    }
}
