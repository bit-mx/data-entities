<?php

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\MockResponseSequence;

afterEach(function () {
    DataEntity::resetMock();
});

it('supports conditional fakes based on pending query parameters', function () {
    $dataEntity = new #[SingleItemResponse] class(1) extends DataEntity
    {
        public function __construct(private int $postId) {}

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                'post_id' => $this->postId,
            ];
        }
    };

    DataEntity::fake([
        $dataEntity::class => function (PendingQuery $pendingQuery) {
            return MockResponse::make([
                'id' => $pendingQuery->parameters()->get('post_id'),
            ]);
        },
    ]);

    expect($dataEntity->execute()->data('id'))->toBe(1);
});

it('supports sequenced mock responses', function () {
    $dataEntity = new #[SingleItemResponse] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponseSequence::make(
            MockResponse::make(['id' => 1]),
            MockResponse::make(['id' => 2]),
        ),
    ]);

    expect($dataEntity->execute()->data('id'))->toBe(1)
        ->and($dataEntity->execute()->data('id'))->toBe(2);
});

it('asserts executions with expected parameters', function () {
    $dataEntity = new #[SingleItemResponse] class(10) extends DataEntity
    {
        public function __construct(private int $postId) {}

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                'post_id' => $this->postId,
                'status' => 'active',
            ];
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make(['ok' => true]),
    ]);

    $dataEntity->execute();

    DataEntity::assertExecutedWith($dataEntity::class, ['post_id' => 10]);
    DataEntity::assertExecutedWith($dataEntity::class, fn (array $parameters) => $parameters['status'] === 'active');
});
