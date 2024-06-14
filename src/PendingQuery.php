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
use BitMx\DataEntities\Traits\DataEntity\HasMiddleware;
use BitMx\DataEntities\Traits\HasOutputParameters;
use BitMx\DataEntities\Traits\HasParameters;
use BitMx\DataEntities\Traits\HasQueryStatements;
use BitMx\DataEntities\Traits\PendingQuery\HasAccessorsStore;
use BitMx\DataEntities\Traits\PendingQuery\HasAliasStore;
use BitMx\DataEntities\Traits\PendingQuery\HasMutatorStore;
use BitMx\DataEntities\Traits\PendingQuery\Tappable;
use BitMx\DataEntities\Traits\Response\FakeResponse;

class PendingQuery
{
    use HasAccessorsStore;
    use HasAliasStore;
    use HasMiddleware;
    use HasMutatorStore;
    use HasOutputParameters;
    use HasParameters;
    use HasQueryStatements;
    use Tappable;

    protected readonly Method $method;

    protected ?FakeResponse $fakeResponse = null;

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

    public function hasFakeResponse(): bool
    {
        return ! is_null($this->fakeResponse);
    }

    public function getFakeResponse(): FakeResponse
    {
        return $this->fakeResponse;
    }

    public function setFakeResponse(FakeResponse $fakeResponse): self
    {
        $this->fakeResponse = $fakeResponse;

        return $this;
    }
}
