<?php

use BitMx\DataEntities\DataEntity;

it('defaults query timeout to null', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    expect($dataEntity->queryTimeout())->toBeNull();
});

it('allows overriding query timeout per entity', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function queryTimeout(): ?int
        {
            return 30;
        }
    };

    expect($dataEntity->queryTimeout())->toBe(30);
});
