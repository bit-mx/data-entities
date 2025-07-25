<?php

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Plugins\AlwaysThrowOnError;
use BitMx\DataEntities\Responses\MockResponse;

it('creates a data entity', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    expect($dataEntity->resolveStoreProcedure())->toBe('sp_test');
});

test('if is not enable fake then DataEntity is not fakeable', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    expect($dataEntity::isFake())->toBeFalse();
});

test('if fake is enabled fake then DataEntity is fakeable', function () {
    DataEntity::fake();

    $dataEntity = new class extends DataEntity
    {
        use AlwaysThrowOnError;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::makeWithException(new Exception('Error')),
    ]);

    $dataEntity->execute();

    DataEntity::assertExecuted($dataEntity::class);
})
    ->throws(Exception::class, 'Error');
