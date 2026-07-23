<?php

declare(strict_types=1);

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Exceptions\DuplicatePipeNameException;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Pipelines\MiddlewarePipeline;
use BitMx\DataEntities\Pipelines\Pipeline;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\Response;

afterEach(function () {
    DataEntity::resetMock();
});

it('processes pipes in order', function () {
    $pipeline = new Pipeline;

    $pipeline->addPipe(fn (int $value): int => $value + 1);
    $pipeline->addPipe(fn (int $value): int => $value * 2);
    $pipeline->addPipe(fn (int $value): int => $value - 3);

    expect($pipeline->process(5))->toBe(9);
});

it('throws DuplicatePipeNameException for duplicate named pipes', function () {
    $pipeline = new Pipeline;

    $pipeline->addPipe(fn ($value) => $value, 'named');
    $pipeline->addPipe(fn ($value) => $value, 'named');
})->throws(DuplicatePipeNameException::class);

it('allows unnamed duplicate callables', function () {
    $pipeline = new Pipeline;

    $pipeline->addPipe(fn (int $value): int => $value + 1);
    $pipeline->addPipe(fn (int $value): int => $value + 1);

    expect($pipeline->process(1))->toBe(3)
        ->and($pipeline->getPipes())->toHaveCount(2);
});

it('runs query and response middleware pipelines', function () {
    $dataEntity = new #[SingleItemResponse] class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make(['id' => 1]),
    ]);

    $pendingQuery = new PendingQuery($dataEntity);
    $middleware = new MiddlewarePipeline;

    $middleware->onQuery(function (PendingQuery $query) {
        $query->parameters()->add('from_middleware', true);

        return $query;
    }, 'query-mw');

    $middleware->onResponse(function (Response $response) {
        return $response;
    }, 'response-mw');

    $resultQuery = $middleware->executeQueryPipeline($pendingQuery);

    expect($resultQuery->parameters()->get('from_middleware'))->toBeTrue()
        ->and($middleware->getQueryPipeline()->getPipes())->toHaveCount(1)
        ->and($middleware->getResponsePipeline()->getPipes())->toHaveCount(1);

    $response = $dataEntity->execute();
    $middleware->executeResponsePipeline($response);
});

it('merges middleware pipelines', function () {
    $first = new MiddlewarePipeline;
    $second = new MiddlewarePipeline;

    $first->onQuery(fn (PendingQuery $query) => $query, 'first');
    $second->onQuery(fn (PendingQuery $query) => $query, 'second');
    $second->onResponse(fn (Response $response) => $response, 'response');

    $first->merge($second);

    expect($first->getQueryPipeline()->getPipes())->toHaveCount(2)
        ->and($first->getResponsePipeline()->getPipes())->toHaveCount(1);
});
