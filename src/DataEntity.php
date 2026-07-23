<?php

declare(strict_types=1);

namespace BitMx\DataEntities;

use BitMx\DataEntities\Traits\DataEntity\Assertable;
use BitMx\DataEntities\Traits\DataEntity\Bootable;
use BitMx\DataEntities\Traits\DataEntity\ExecutesQuery;
use BitMx\DataEntities\Traits\DataEntity\HasAccessors;
use BitMx\DataEntities\Traits\DataEntity\HasAlias;
use BitMx\DataEntities\Traits\DataEntity\HasConnection;
use BitMx\DataEntities\Traits\DataEntity\HasDumpableQuery;
use BitMx\DataEntities\Traits\DataEntity\HasFakeableResponse;
use BitMx\DataEntities\Traits\DataEntity\HasFakeResponse;
use BitMx\DataEntities\Traits\DataEntity\HasMiddleware;
use BitMx\DataEntities\Traits\DataEntity\HasMutators;
use BitMx\DataEntities\Traits\DataEntity\HasQueryExecutor;
use BitMx\DataEntities\Traits\DataEntity\HasQueryTimeout;
use BitMx\DataEntities\Traits\DataEntity\HasRequiredParameters;
use BitMx\DataEntities\Traits\DataEntity\HasTransactions;
use BitMx\DataEntities\Traits\DataEntity\MapsToDto;
use BitMx\DataEntities\Traits\HasOutputParameters;
use BitMx\DataEntities\Traits\HasParameters;
use BitMx\DataEntities\Traits\HasQueryStatements;

abstract class DataEntity
{
    use Assertable;
    use Bootable;
    use ExecutesQuery;
    use HasAccessors;
    use HasAlias;
    use HasConnection;
    use HasDumpableQuery;
    use HasFakeableResponse;
    use HasFakeResponse;
    use HasMiddleware;
    use HasMutators;
    use HasOutputParameters;
    use HasParameters;
    use HasQueryExecutor;
    use HasQueryStatements;
    use HasQueryTimeout;
    use HasRequiredParameters;
    use HasTransactions;
    use MapsToDto;

    abstract public function resolveStoreProcedure(): string;
}
