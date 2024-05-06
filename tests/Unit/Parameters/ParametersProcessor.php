<?php

use BitMx\DataEntities\Parameters\ParametersProcessor;
use BitMx\DataEntities\Stores\ArrayStore;
use BitMx\DataEntities\Tests\Helpers\IntEnum;
use BitMx\DataEntities\Tests\Helpers\StringEnum;

it('create a parameter with int', function () {
    $data = new ArrayStore([
        'id' => 1,
    ]);

    $parametersProcessor = new ParametersProcessor();

    $parameters = $parametersProcessor->process($data);

    expect(is_int($parameters['id']))->toBeTrue()
        ->and($parameters['id'])->toBe(1);
});

it('create a parameter with string   ', function () {
    $data = new ArrayStore([
        'name' => 'John Doe',
    ]);

    $parametersProcessor = new ParametersProcessor();

    $parameters = $parametersProcessor->process($data);

    expect(is_string($parameters['name']))->toBeTrue()
        ->and($parameters['name'])->toBe('John Doe');
});

it('create a parameter with bool', function () {
    $data = new ArrayStore([
        'active' => true,
    ]);

    $parametersProcessor = new ParametersProcessor();

    $parameters = $parametersProcessor->process($data);

    expect(is_int($parameters['active']))->toBeTrue()
        ->and($parameters['active'])->toBe(1);

    $data = new ArrayStore([
        'active' => false,
    ]);

    $parametersProcessor = new ParametersProcessor();

    $parameters = $parametersProcessor->process($data);

    expect(is_int($parameters['active']))->toBeTrue()
        ->and($parameters['active'])->toBe(0);
});

it('create a parameter with string backed enum', function () {
    $data = new ArrayStore([
        'status' => StringEnum::PAID,
    ]);

    $parametersProcessor = new ParametersProcessor();

    $parameters = $parametersProcessor->process($data);

    expect(is_string($parameters['status']))->toBeTrue()
        ->and($parameters['status'])->toBe('paid');
});

it('create a parameter with int backed enum', function () {
    $data = new ArrayStore([
        'status' => IntEnum::ALL,
    ]);

    $parametersProcessor = new ParametersProcessor();

    $parameters = $parametersProcessor->process($data);

    expect(is_int($parameters['status']))->toBeTrue()
        ->and($parameters['status'])->toBe(1);
});
