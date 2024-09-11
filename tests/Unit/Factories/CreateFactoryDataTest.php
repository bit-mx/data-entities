<?php

use BitMx\DataEntities\Factories\CreateFactoryData;
use BitMx\DataEntities\Factories\DataEntityFactory;

it('creates a new instance of CreateFactoryData', function () {
    $createFactoryData = new CreateFactoryData;

    expect($createFactoryData)->toBeInstanceOf(CreateFactoryData::class);

});

it('gets the data from the factory', function () {
    $factory = new class extends DataEntityFactory
    {
        public function definition(): array
        {
            return ['name' => 'John Doe'];
        }
    };

    $createFactoryData = new CreateFactoryData;

    expect($createFactoryData($factory))->toBe(['name' => 'John Doe']);

});
