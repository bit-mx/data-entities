<?php

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Events\DataEntityExecuted;
use BitMx\DataEntities\Events\DataEntityFailed;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Processors\Processor;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Event;
use ReflectionMethod;

it('dispatches DataEntityExecuted when the query succeeds', function () {
    Event::fake([DataEntityExecuted::class, DataEntityFailed::class]);

    $dataEntity = new #[SingleItemResponse] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);
    $processor = new Processor($pendingQuery);
    $response = new Response($pendingQuery, ['id' => 1], [], true);

    $method = new ReflectionMethod($processor, 'dispatchExecutionEvent');
    $method->invoke($processor, $response, 'EXEC sp_test ; ', hrtime(true) - 1_000_000, null);

    Event::assertDispatched(DataEntityExecuted::class, function (DataEntityExecuted $event) use ($dataEntity, $response) {
        return $event->dataEntity === $dataEntity
            && $event->response === $response
            && $event->query === 'EXEC sp_test ; '
            && $event->durationMs >= 0;
    });

    Event::assertNotDispatched(DataEntityFailed::class);
});

it('dispatches DataEntityFailed when the query fails', function () {
    Event::fake([DataEntityExecuted::class, DataEntityFailed::class]);

    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);
    $processor = new Processor($pendingQuery);
    $exception = new QueryException('sqlsrv', 'EXEC sp_test', [], new Exception('boom'));
    $response = new Response($pendingQuery, [], [], false, $exception);

    $method = new ReflectionMethod($processor, 'dispatchExecutionEvent');
    $method->invoke($processor, $response, 'EXEC sp_test ; ', hrtime(true) - 1_000_000, $exception);

    Event::assertDispatched(DataEntityFailed::class, function (DataEntityFailed $event) use ($exception) {
        return $event->exception === $exception
            && $event->query === 'EXEC sp_test ; ';
    });

    Event::assertNotDispatched(DataEntityExecuted::class);
});
