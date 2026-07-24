<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Testing;

use BitMx\DataEntities\PendingQuery;

final readonly class RecordedExecution
{
    /**
     * @param  class-string  $class
     * @param  array<array-key, mixed>  $parameters
     * @param  array<array-key, mixed>  $outputParameters
     */
    public function __construct(
        public string $class,
        public string $procedure,
        public array $parameters,
        public array $outputParameters,
        public PendingQuery $pendingQuery,
    ) {}

    /**
     * @param  array<array-key, mixed>  $expected
     */
    public function parametersMatch(array $expected): bool
    {
        foreach ($expected as $key => $value) {
            if (! array_key_exists($key, $this->parameters) || $this->parameters[$key] !== $value) {
                return false;
            }
        }

        return true;
    }
}
