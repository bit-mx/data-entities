<?php

namespace BitMx\DataEntities\Traits\DataEntity;

trait HasConnection
{
    public function resolveDatabaseConnection(): string
    {
        return config('data-entities.database', 'sqlsrv');
    }
}
