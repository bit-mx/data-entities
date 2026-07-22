<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Processors;

use BitMx\DataEntities\Contracts\ProcessorContract;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Exceptions\MockResponseNotFoundException;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\MockResponseSequence;
use BitMx\DataEntities\Responses\Response;
use Closure;
use Illuminate\Support\LazyCollection;

class MockProcessor implements ProcessorContract
{
    /**
     * @param  array<class-string, MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse>  $mockResponses
     */
    public function __construct(
        protected readonly PendingQuery $pendingQuery,
        protected DataEntity $dataEntity,
        protected readonly array $mockResponses,
    ) {}

    public function handle(): Response
    {
        $class = get_class($this->dataEntity);

        if (! array_key_exists($class, $this->mockResponses)) {
            throw new MockResponseNotFoundException('No mock response found for '.get_class($this));
        }

        return $this->executeMockResponse();
    }

    protected function executeMockResponse(): Response
    {
        $class = get_class($this->dataEntity);

        if (! array_key_exists($class, $this->mockResponses)) {
            throw new MockResponseNotFoundException('No mock response found for '.get_class($this));
        }

        $mockResponse = $this->resolveMockResponse($class);

        DataEntity::$assertions[$class] = (DataEntity::$assertions[$class] ?? 0) + 1;
        DataEntity::$recordedParameters[$class][] = $this->pendingQuery->parameters()->all();

        return $this->createFakeResponse($mockResponse);
    }

    protected function resolveMockResponse(string $class): MockResponse
    {
        /** @var MockResponse|MockResponseSequence|Closure(PendingQuery): MockResponse $mock */
        $mock = $this->mockResponses[$class];

        if ($mock instanceof Closure) {
            return $mock($this->pendingQuery);
        }

        if ($mock instanceof MockResponseSequence) {
            return $mock->next();
        }

        return $mock;
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
