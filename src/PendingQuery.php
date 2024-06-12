<?php

namespace BitMx\DataEntities;

use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\PendingQuery\AddAccessors;
use BitMx\DataEntities\PendingQuery\AddAlias;
use BitMx\DataEntities\PendingQuery\AddMutators;
use BitMx\DataEntities\PendingQuery\BootDataEntity;
use BitMx\DataEntities\PendingQuery\BootTraits;
use BitMx\DataEntities\PendingQuery\MergeMiddlewares;
use BitMx\DataEntities\PendingQuery\MergeOutputParameters;
use BitMx\DataEntities\PendingQuery\MergeParameters;
use BitMx\DataEntities\PendingQuery\MergeQueryStatements;
use BitMx\DataEntities\Traits\DataEntity\HasAlias;
use BitMx\DataEntities\Traits\DataEntity\HasMiddleware;
use BitMx\DataEntities\Traits\HasOutputParameters;
use BitMx\DataEntities\Traits\HasParameters;
use BitMx\DataEntities\Traits\HasQueryStatements;
use BitMx\DataEntities\Traits\PendingQuery\HasAccessorsStore;
use BitMx\DataEntities\Traits\PendingQuery\HasMutatorStore;
use BitMx\DataEntities\Traits\PendingQuery\Tappable;

class PendingQuery
{
    use HasAccessorsStore;
    use HasAlias;
    use HasMiddleware;
    use HasMutatorStore;
    use HasOutputParameters;
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
            ->tap(new MergeOutputParameters)
            ->tap(new MergeQueryStatements)
            ->tap(new MergeMiddlewares)
            ->tap(new AddMutators)
            ->tap(new AddAccessors)
            ->tap(new AddAlias);

        // Execute the middleware
        $this->middleware()->executeQueryPipeline($this);
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
