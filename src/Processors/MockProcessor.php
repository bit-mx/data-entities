<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Processors;

use BitMx\DataEntities\Contracts\ProcessorContract;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Testing\MockClient;
use BitMx\DataEntities\Testing\RecordedExecution;
use Illuminate\Support\LazyCollection;

class MockProcessor implements ProcessorContract
{
    public function __construct(
        protected readonly PendingQuery $pendingQuery,
        protected DataEntity $dataEntity,
        protected readonly MockClient $mockClient,
    ) {}

    public function handle(): Response
    {
        $class = $this->dataEntity::class;
        $mockResponse = $this->mockClient->resolve($class, $this->pendingQuery);

        $this->mockClient->record(new RecordedExecution(
            class: $class,
            procedure: $this->dataEntity->resolveStoreProcedure(),
            parameters: $this->pendingQuery->parameters()->all(),
            outputParameters: $this->pendingQuery->outputParameters()->all(),
            pendingQuery: $this->pendingQuery,
        ));

        return $this->createFakeResponse($mockResponse);
    }

    protected function createFakeResponse(MockResponse $mockResponse): Response
    {
        if ($mockResponse->hasException()) {
            return new Response($this->pendingQuery, [], [], false, $mockResponse->exception());
        }

        if (! $this->pendingQuery->usesLazyCollection()) {
            return new Response($this->pendingQuery, $mockResponse->data(), $mockResponse->output(), true);
        }

        return new Response($this->pendingQuery, [], [], true, null, LazyCollection::make($mockResponse->data()));
    }
}
