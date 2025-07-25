<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Processors;

use BitMx\DataEntities\Contracts\ProcessorContract;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Exceptions\MockResponseNotFoundException;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\LazyCollection;

class MockProcessor implements ProcessorContract
{
    /**
     * @param  array<class-string, MockResponse>  $mockResponses
     */
    public function __construct(
        protected readonly PendingQuery $pendingQuery,
        protected DataEntity $dataEntity,
        protected readonly array $mockResponses,
    ) {}

    public function handle(): Response
    {
        if (! Arr::has($this->mockResponses, get_class($this->dataEntity))) {
            throw new MockResponseNotFoundException('No mock response found for '.get_class($this));
        }

        return $this->executeMockResponse();
    }

    protected function executeMockResponse(): Response
    {
        if (! Arr::has($this->mockResponses, get_class($this->dataEntity))) {
            throw new MockResponseNotFoundException('No mock response found for '.get_class($this));
        }

        $mockResponse = Arr::get($this->mockResponses, get_class($this->dataEntity));

        Arr::set(DataEntity::$assertions, get_class($this->dataEntity), Arr::get(DataEntity::$assertions, get_class($this->dataEntity), 0) + 1);

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
