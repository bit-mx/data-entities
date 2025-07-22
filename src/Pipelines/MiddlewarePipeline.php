<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Pipelines;

use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;

class MiddlewarePipeline
{
    /**
     * @var Pipeline<PendingQuery>
     */
    protected Pipeline $queryPipeline;

    /**
     * @var Pipeline<Response>
     */
    protected Pipeline $responsePipeline;

    public function __construct()
    {
        $this->queryPipeline = new Pipeline;
        $this->responsePipeline = new Pipeline;
    }

    public function onQuery(callable $callable, ?string $name = null): static
    {
        $this->queryPipeline->addPipe(static function (PendingQuery $pendingQuery) use ($callable): PendingQuery {

            $result = $callable($pendingQuery);

            return $result instanceof PendingQuery ? $result : $pendingQuery;
        }, $name);

        return $this;
    }

    public function onResponse(callable $callable, ?string $name = null): static
    {
        $this->responsePipeline->addPipe(static function (Response $response) use ($callable): Response {
            $result = $callable($response);

            return $result instanceof Response ? $result : $response;
        }, $name);

        return $this;
    }

    public function executeQueryPipeline(PendingQuery $pendingQuery): PendingQuery
    {
        return $this->queryPipeline->process($pendingQuery);
    }

    public function executeResponsePipeline(Response $response): Response
    {
        return $this->responsePipeline->process($response);
    }

    public function merge(MiddlewarePipeline $middlewarePipeline): static
    {
        $queryPipelines = array_merge(
            $this->getQueryPipeline()->getPipes(),
            $middlewarePipeline->getQueryPipeline()->getPipes()
        );

        $responsePipelines = array_merge(
            $this->getResponsePipeline()->getPipes(),
            $middlewarePipeline->getResponsePipeline()->getPipes()
        );

        $this->getQueryPipeline()->setPipes($queryPipelines);

        $this->getResponsePipeline()->setPipes($responsePipelines);

        return $this;
    }

    /**
     * @return Pipeline<PendingQuery>
     */
    public function getQueryPipeline(): Pipeline
    {
        return $this->queryPipeline;
    }

    /**
     * @return Pipeline<Response>
     */
    public function getResponsePipeline(): Pipeline
    {
        return $this->responsePipeline;
    }
}
