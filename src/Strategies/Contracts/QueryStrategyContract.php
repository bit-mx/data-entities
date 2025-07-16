<?php

namespace BitMx\DataEntities\Strategies\Contracts;

use Illuminate\Database\Connection;
use Illuminate\Support\LazyCollection;

interface QueryStrategyContract
{
    /**
     * @param  array<string, string>  $params
     * @return array<array-key,mixed>|LazyCollection<array-key,mixed>
     */
    public function execute(Connection $client, string $query, array $params = []): array|LazyCollection;
}
