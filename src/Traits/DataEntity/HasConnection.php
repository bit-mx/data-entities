<?php

namespace BitMx\DataEntities\Traits\DataEntity;

use Illuminate\Support\Facades\Config;

trait HasConnection
{
    public function resolveDatabaseConnection(): string
    {
        return Config::get('database.default', 'sqlsrv');
    }
}
