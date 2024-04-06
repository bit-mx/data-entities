<?php

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\Contracts\ProcessorContract;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Processors\MockProcessor;
use BitMx\DataEntities\Processors\Processor;
use BitMx\DataEntities\Responses\Response;

/**
 * @mixin DataEntity
 */
trait ExecutesQuery
{
    public function execute(): Response
    {
        return $this->resolveResponse();
    }

    protected function resolveResponse(): Response
    {
        return $this->executeQuery();
    }

    protected function executeQuery(): Response
    {
        $executer = $this->resolveClient();

        $reponse = $executer->handle();

        return $reponse;
    }

    protected function resolveClient(): ProcessorContract
    {
        if (static::isFake()) {
            return new MockProcessor($this->createPendingQuery(), $this, static::$mockResponses);
        }

        return new Processor($this->createPendingQuery());
    }

    protected function createPendingQuery(): PendingQuery
    {
        $pendingQuery = new PendingQuery($this);

        return $pendingQuery;
    }
}
