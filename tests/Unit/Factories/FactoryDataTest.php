<?php

use BitMx\DataEntities\Factories\FactoryData;

test('creates a new instance of FactoryData', function () {
    $factoryData = new FactoryData(
        definition: ['name' => 'John Doe'],
        attributes: ['email' => 'test@example.com', 'name' => 'Jane Doe'],
        without: ['name'],
    );

    expect($factoryData)->toBeInstanceOf(FactoryData::class);
});

it('gets the data merged between definition and attributes', function () {
    $factoryData = new FactoryData(
        definition: ['name' => 'John Doe'],
        attributes: ['email' => 'test@example.com', 'name' => 'Jane Doe'],
        without: [],
    );

    expect($factoryData->getData())->toBe([
        'name' => 'Jane Doe',
        'email' => 'test@example.com',
    ]);
});

it('get the data without the specified keys', function () {
    $factoryData = new FactoryData(
        definition: ['name' => 'John Doe'],
        attributes: ['email' => 'test@example.com', 'name' => 'Jane Doe'],
        without: ['name'],
    );

    expect($factoryData->getData())->toBe([
        'email' => 'test@example.com',
    ]);
});
