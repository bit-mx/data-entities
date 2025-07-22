<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

trait HasConnection
{
    public function resolveDatabaseConnection(): string
    {
        return config('data-entities.database', 'sqlsrv');
    }
}
