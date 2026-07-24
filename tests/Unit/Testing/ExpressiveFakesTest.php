<?php

declare(strict_types=1);

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Exceptions\MockResponseNotFoundException;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\MockResponseSequence;
use BitMx\DataEntities\Testing\RecordedExecution;
use Exception;
use RuntimeException;

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

it('reuses whenEmpty response after a sequence is exhausted', function () {
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
        )->whenEmpty(MockResponse::make(['id' => 99])),
    ]);

    expect($dataEntity->execute()->data('id'))->toBe(1)
        ->and($dataEntity->execute()->data('id'))->toBe(99)
        ->and($dataEntity->execute()->data('id'))->toBe(99);
});

it('merges additional mocks through the static facade', function () {
    $primary = new #[SingleItemResponse] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_primary';
        }
    };

    $secondary = new #[SingleItemResponse] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_secondary';
        }
    };

    DataEntity::fake([
        $primary::class => MockResponse::make(['from' => 'primary']),
    ]);

    DataEntity::mock([
        $secondary::class => MockResponse::make(['from' => 'secondary']),
    ]);

    expect($primary->execute()->data('from'))->toBe('primary')
        ->and($secondary->execute()->data('from'))->toBe('secondary');
});

it('uses fallback when a class is not mocked', function () {
    $dataEntity = new #[SingleItemResponse] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([]);
    DataEntity::fallback(MockResponse::make(['fallback' => true]));

    expect($dataEntity->execute()->data('fallback'))->toBeTrue();
});

it('records executions for inspection via the static facade', function () {
    $dataEntity = new #[SingleItemResponse] class(10) extends DataEntity
    {
        public function __construct(private int $postId) {}

        public function resolveStoreProcedure(): string
        {
            return 'sp_get_post';
        }

        protected function defaultParameters(): array
        {
            return [
                'post_id' => $this->postId,
            ];
        }

        protected function defaultOutputParameters(): array
        {
            return [
                'total' => 'INT',
            ];
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make(['ok' => true])->withOutput(['total' => 5]),
    ]);

    $response = $dataEntity->execute();

    expect($response->output('total'))->toBe(5)
        ->and(DataEntity::recorded())->toHaveCount(1)
        ->and(DataEntity::recorded($dataEntity::class)[0])->toBeInstanceOf(RecordedExecution::class)
        ->and(DataEntity::recorded($dataEntity::class)[0]->procedure)->toBe('sp_get_post')
        ->and(DataEntity::recorded($dataEntity::class)[0]->parameters)->toBe(['post_id' => 10])
        ->and(DataEntity::recorded($dataEntity::class)[0]->outputParameters)->toBe(['total' => 'INT']);
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
    DataEntity::assertExecutedWith(
        $dataEntity::class,
        fn (RecordedExecution $execution) => $execution->procedure === 'sp_test' && $execution->parameters['post_id'] === 10,
    );
});

it('asserts nothing was executed without calling fake first', function () {
    DataEntity::assertNothingExecuted();
    DataEntity::assertNotExecuted(DataEntity::class);
});

it('asserts total executed count and order', function () {
    $primary = new #[SingleItemResponse] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_primary';
        }
    };

    $secondary = new #[SingleItemResponse] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_secondary';
        }
    };

    DataEntity::fake([
        $primary::class => MockResponse::empty(),
        $secondary::class => MockResponse::empty(),
    ]);

    $primary->execute();
    $secondary->execute();

    DataEntity::assertTotalExecutedCount(2);
    DataEntity::assertExecutedInOrder([$primary::class, $secondary::class]);
});

it('provides fluent output and exception helpers on MockResponse', function () {
    $withOutput = MockResponse::make(['title' => 'New post'], ['p_new_id' => 42]);
    $fluent = MockResponse::make(['title' => 'New post'])->withOutput(['p_new_id' => 42]);
    $exception = MockResponse::make(['id' => 1])->withException(new RuntimeException('boom'));

    expect(MockResponse::empty()->data())->toBe([])
        ->and($withOutput->output())->toBe(['p_new_id' => 42])
        ->and($fluent->output())->toBe(['p_new_id' => 42])
        ->and($exception->hasException())->toBeTrue()
        ->and($exception->exception())->toBeInstanceOf(RuntimeException::class);
});

it('throws when a sequence is exhausted without whenEmpty', function () {
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
        ),
    ]);

    $dataEntity->execute();

    $dataEntity->execute();
})->throws(MockResponseNotFoundException::class);

it('reports useful assertion failures', function () {
    $dataEntity = new #[SingleItemResponse] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return ['post_id' => 1];
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make(['ok' => true]),
    ]);

    $dataEntity->execute();

    expect(fn () => DataEntity::assertExecutedCount($dataEntity::class, 2))
        ->toThrow(Exception::class, 'executed 2 time(s)');

    expect(fn () => DataEntity::assertExecutedWith($dataEntity::class, ['post_id' => 99]))
        ->toThrow(Exception::class, 'Last recorded parameters');
});
