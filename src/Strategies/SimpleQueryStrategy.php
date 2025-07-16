<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Strategies;

use BitMx\DataEntities\Strategies\Contracts\QueryStrategyContract;
use Illuminate\Database\Connection;

class SimpleQueryStrategy implements QueryStrategyContract
{
    /**
     * @param  array<array-key, mixed>  $params
     * @return array<array-key, mixed>
     */
    public function execute(Connection $client, string $query, array $params = []): array
    {
        return $client->selectResultSets($query, $params);
    }
}
