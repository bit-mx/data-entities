<?php

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Executers\Executer;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Arr;

/**
 * @mixin DataEntity
 */
trait ExecutesQuery
{
    public function execute(): Response
    {
        return $this->resolveResponse();
    }

    protected function createPendingQuery(): PendingQuery
    {
        $pendingQuery = new PendingQuery($this);

        return $pendingQuery;
    }

    protected function resolveResponse(): Response
    {
        if (static::isFake()) {
            return $this->executeMockResponse();
        }

        return $this->executeQuery();
    }

    protected function executeMockResponse(): Response
    {
        if (! Arr::has(static::$mockResponses, get_class($this))) {
            return $this->createFakeResponse(MockResponse::make([]));
        }

        $mockResponse = Arr::get(static::$mockResponses, get_class($this));

        return $this->createFakeResponse($mockResponse);
    }

    protected function createFakeResponse(MockResponse $mockResponse): Response
    {
        return new Response($this, $mockResponse->data(), true);
    }

    protected function executeQuery(): Response
    {
        $executer = new Executer($this->createPendingQuery());

        $reponse = $executer->exec();

        return $reponse;
    }
}
