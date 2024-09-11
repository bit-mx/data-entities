<?php

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Plugins\AlwaysThrowOnError;
use BitMx\DataEntities\Responses\MockResponse;

it('throw error on response', function () {
    $dataEntity = new class extends DataEntity
    {
        use AlwaysThrowOnError;

        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::makeWithException(new Exception('Error')),
    ]);

    $dataEntity->execute();
})
    ->throws(Exception::class, 'Error');
