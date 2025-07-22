<?php

declare(strict_types=1);

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
        $pendingQuery = $this->createPendingQuery();

        $executer = $this->resolveClient($pendingQuery);

        $response = $pendingQuery->hasFakeResponse()
            ? $this->createFakeResponse($pendingQuery->getFakeResponse())
            : $executer->handle();

        $response = $response->getPendingQuery()->middleware()->executeResponsePipeline($response);

        return $response;
    }

    protected function createPendingQuery(): PendingQuery
    {
        return new PendingQuery($this);
    }

    protected function resolveClient(PendingQuery $pendingQuery): ProcessorContract
    {
        if (static::isFake()) {
            return new MockProcessor($pendingQuery, $this, static::$mockResponses);
        }

        return new Processor($pendingQuery);
    }
}
