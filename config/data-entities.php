<?php

declare(strict_types=1);

use BitMx\DataEntities\Executers\MySqlQueryExecutor;
use BitMx\DataEntities\Executers\SqlServerQueryExecutor;

return [
    'database' => env('DATA_ENTITIES_CONNECTION', 'sqlsrv'),

    'executers' => [
        'sqlsrv' => SqlServerQueryExecutor::class,
        'mysql' => MySqlQueryExecutor::class,
    ],
];
