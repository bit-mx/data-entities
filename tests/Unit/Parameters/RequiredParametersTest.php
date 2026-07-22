<?php

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Exceptions\MissingRequiredParameterException;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Processors\Processor;
use ReflectionMethod;

it('passes when all required parameters are present', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function requiredParameters(): array
        {
            return ['post_id'];
        }

        protected function defaultParameters(): array
        {
            return [
                'post_id' => 1,
            ];
        }
    };

    $processor = new Processor(new PendingQuery($dataEntity));
    $method = new ReflectionMethod($processor, 'validateRequiredParameters');

    $method->invoke($processor);

    expect(true)->toBeTrue();
});

it('throws when a required parameter is missing', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function requiredParameters(): array
        {
            return ['post_id', 'status'];
        }

        protected function defaultParameters(): array
        {
            return [
                'post_id' => 1,
            ];
        }
    };

    $processor = new Processor(new PendingQuery($dataEntity));
    $method = new ReflectionMethod($processor, 'validateRequiredParameters');

    $method->invoke($processor);
})->throws(MissingRequiredParameterException::class, 'Missing required parameter [status].');
