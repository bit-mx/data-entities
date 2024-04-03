<?php

namespace BitMx\DataEntities;

use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\PendingQuery\BootDataEntity;
use BitMx\DataEntities\PendingQuery\BootTraits;
use BitMx\DataEntities\PendingQuery\MergeParameters;
use BitMx\DataEntities\Traits\HasParameters;
use BitMx\DataEntities\Traits\PendingQuery\Tappable;

class PendingQuery
{
    use HasParameters;
    use Tappable;

    protected Method $method;

    public function __construct(protected DataEntity $dataEntity)
    {
        $this
            ->tap(new BootDataEntity)
            ->tap(new BootTraits)
            ->tap(new MergeParameters);
    }

    public function getMethod(): Method
    {
        return $this->dataEntity->getMethod();
    }

    public function getDataEntity(): DataEntity
    {
        return $this->dataEntity;
    }
}
