<?php

declare(strict_types=1);

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Plugins\HasRetries;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\MockResponseSequence;
use Carbon\CarbonInterval;
use Illuminate\Database\QueryException;

afterEach(function () {
    DataEntity::resetMock();
});

it('retries transient failures and returns the successful response', function () {
    $dataEntity = new #[SingleItemResponse] class extends DataEntity
    {
        use HasRetries;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponseSequence::make(
            MockResponse::makeWithException(new QueryException('sqlsrv', 'EXEC sp_test', [], new Exception('deadlock 1205'))),
            MockResponse::make(['id' => 1]),
        ),
    ]);

    $response = $dataEntity->execute();

    expect($response->success())->toBeTrue()
        ->and($response->data('id'))->toBe(1);

    DataEntity::assertExecutedCount($dataEntity::class, 2);
});

it('accepts a CarbonInterval as retry backoff', function () {
    $dataEntity = new #[SingleItemResponse] class extends DataEntity
    {
        use HasRetries;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function retryBackoff(): int|CarbonInterval
        {
            return CarbonInterval::milliseconds(1);
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponseSequence::make(
            MockResponse::makeWithException(new QueryException('sqlsrv', 'EXEC sp_test', [], new Exception('deadlock 1205'))),
            MockResponse::make(['id' => 1]),
        ),
    ]);

    $started = hrtime(true);
    $response = $dataEntity->execute();
    $elapsedMs = (hrtime(true) - $started) / 1_000_000;

    expect($response->success())->toBeTrue()
        ->and($elapsedMs)->toBeGreaterThanOrEqual(1);

    DataEntity::assertExecutedCount($dataEntity::class, 2);
});

it('does not retry non-transient failures', function () {
    $dataEntity = new #[SingleItemResponse] class extends DataEntity
    {
        use HasRetries;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::makeWithException(
            new QueryException('sqlsrv', 'EXEC sp_test', [], new Exception('Invalid object name'))
        ),
    ]);

    $response = $dataEntity->execute();

    expect($response->failed())->toBeTrue();

    DataEntity::assertExecutedCount($dataEntity::class, 1);
});
