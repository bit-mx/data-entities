<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Pipelines;

use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;

readonly class Pipe
{
    protected \Closure $callable;

    /**
     * Constructor
     *
     * @param  callable(Response|PendingQuery $payload): (Response|PendingQuery)  $callable
     */
    public function __construct(
        callable $callable,

        protected ?string $name = null,
    ) {
        $this->callable = $callable(...);
    }

    public function getCallable(): \Closure
    {
        return $this->callable;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
