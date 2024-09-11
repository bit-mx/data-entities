<?php

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Parameters\ParametersProcessor;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Tests\Helpers\IntEnum;
use BitMx\DataEntities\Tests\Helpers\StringEnum;

it('create a parameter with int', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                'id' => 1,
            ];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    $parametersProcessor = new ParametersProcessor($pendingQuery);

    $parameters = $parametersProcessor->process();

    expect(is_int($parameters['id']))->toBeTrue()
        ->and($parameters['id'])->toBe(1);
});

it('create a parameter with string   ', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                'name' => 'John Doe',
            ];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    $parametersProcessor = new ParametersProcessor($pendingQuery);

    $parameters = $parametersProcessor->process();

    expect(is_string($parameters['name']))->toBeTrue()
        ->and($parameters['name'])->toBe('John Doe');
});

it('create a parameter with bool', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                'active' => true,
            ];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    $parametersProcessor = new ParametersProcessor($pendingQuery);

    $parameters = $parametersProcessor->process();

    expect(is_int($parameters['active']))->toBeTrue()
        ->and($parameters['active'])->toBe(1);

});

it('create a parameter with string backed enum', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                'status' => StringEnum::PAID,
            ];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    $parametersProcessor = new ParametersProcessor($pendingQuery);

    $parameters = $parametersProcessor->process();

    expect(is_string($parameters['status']))->toBeTrue()
        ->and($parameters['status'])->toBe('paid');
});

it('create a parameter with int backed enum', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        protected ?ResponseType $responseType = ResponseType::SINGLE;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                'status' => IntEnum::ALL,
            ];
        }
    };

    $pendingQuery = new PendingQuery($dataEntity);

    $parametersProcessor = new ParametersProcessor($pendingQuery);

    $parameters = $parametersProcessor->process();

    expect(is_int($parameters['status']))->toBeTrue()
        ->and($parameters['status'])->toBe(1);
});
