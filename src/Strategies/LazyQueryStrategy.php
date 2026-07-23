<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Strategies;

use BitMx\DataEntities\Strategies\Contracts\QueryStrategyContract;
use Illuminate\Database\Connection;
use Illuminate\Support\LazyCollection;

class LazyQueryStrategy implements QueryStrategyContract
{
    /**
     * @param  array<array-key, mixed>  $params
     * @return LazyCollection<array-key, mixed>
     */
    public function execute(Connection $client, string $query, array $params = []): LazyCollection
    {
        return LazyCollection::make(function () use ($client, $query, $params) {
            foreach ($client->cursor($query, $params) as $row) {
                yield $row;
            }
        });
    }
}
