<?php

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;

it('creates a data entity', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    expect($dataEntity->resolveStoreProcedure())->toBe('sp_test')
        ->and($dataEntity->getMethod())->toBe(Method::SELECT);
});
