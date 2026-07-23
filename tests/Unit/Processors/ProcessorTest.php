<?php

declare(strict_types=1);

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Processors\Processor;
use ReflectionMethod;

it('creates output from subsequent result sets', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultOutputParameters(): array
        {
            return [
                'total' => 'INT',
                'message' => 'NVARCHAR(100)',
            ];
        }
    };

    $processor = new Processor(new PendingQuery($dataEntity));
    $method = new ReflectionMethod($processor, 'createOutput');

    $output = $method->invoke($processor, [
        [['id' => 1]],
        [['total' => 5]],
        [['message' => 'ok']],
    ]);

    expect($output)->toBe([
        'total' => 5,
        'message' => 'ok',
    ]);
});

it('ignores empty output result sets without throwing', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultOutputParameters(): array
        {
            return [
                'total' => 'INT',
            ];
        }
    };

    $processor = new Processor(new PendingQuery($dataEntity));
    $method = new ReflectionMethod($processor, 'createOutput');

    $output = $method->invoke($processor, [
        [['id' => 1]],
        [],
    ]);

    expect($output)->toBe([]);
});
