<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

trait HasConnection
{
    public function resolveDatabaseConnection(): string
    {
        $connection = config('data-entities.database', 'sqlsrv');

        return is_string($connection) && $connection !== '' ? $connection : 'sqlsrv';
    }
}
