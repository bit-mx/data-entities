<?php

namespace BitMx\DataEntities;

use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\PendingQuery\AddAccessors;
use BitMx\DataEntities\PendingQuery\AddMutators;
use BitMx\DataEntities\PendingQuery\BootDataEntity;
use BitMx\DataEntities\PendingQuery\BootTraits;
use BitMx\DataEntities\PendingQuery\MergeParameters;
use BitMx\DataEntities\PendingQuery\MergeQueryStatements;
use BitMx\DataEntities\Traits\DataEntity\HasAccessors;
use BitMx\DataEntities\Traits\DataEntity\HasMutators;
use BitMx\DataEntities\Traits\HasParameters;
use BitMx\DataEntities\Traits\HasQueryStatements;
use BitMx\DataEntities\Traits\PendingQuery\Tappable;

class PendingQuery
{
    use HasAccessors;
    use HasMutators;
    use HasParameters;
    use HasQueryStatements;
    use Tappable;

    protected readonly Method $method;

    public function __construct(protected DataEntity $dataEntity)
    {
        $this->method = $dataEntity->getMethod();

        $this
            ->tap(new BootDataEntity)
            ->tap(new BootTraits)
            ->tap(new MergeParameters)
            ->tap(new MergeQueryStatements)
            ->tap(new AddMutators)
            ->tap(new AddAccessors);
    }

    public function getMethod(): Method
    {
        return $this->method;
    }

    public function getDataEntity(): DataEntity
    {
        return $this->dataEntity;
    }
}
