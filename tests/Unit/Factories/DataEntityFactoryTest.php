<?php

use BitMx\DataEntities\Factories\DataEntityFactory;

beforeEach(function () {
    $this->factory = new class extends DataEntityFactory
    {
        public function definition(): array
        {
            return ['name' => 'John Doe'];
        }
    };
});

it('creates a new instance of DataEntityFactory', function () {

    expect($this->factory)->toBeInstanceOf(DataEntityFactory::class);
});

it('gets the definition from the factory', function () {

    expect($this->factory->create())->toBe(['name' => 'John Doe']);
});

it('creates a new state', function () {
    $factory = $this->factory->state(['name' => 'Jane Doe']);

    expect($factory->create())->toBe(['name' => 'Jane Doe']);
});

it('creates a array with n times the definition', function () {
    $factory = $this->factory->count(3);

    expect($factory->create())->toBe([
        ['name' => 'John Doe'],
        ['name' => 'John Doe'],
        ['name' => 'John Doe'],
    ]);
});

it('get the data without the specified keys', function () {
    $factory = $this->factory->without(['name']);

    expect($factory->create())->toBe([]);
});

it('get attributes  merged with definition', function () {
    $factory = $this->factory
        ->state([
            'email' => 'test@example.com',
            'last_name' => 'Doey',
        ])
        ->without(['last_name']);

    expect($factory->create())->toBe([
        'name' => 'John Doe',
        'email' => 'test@example.com',
    ]);
});

it('overrides the definition with the attributes', function () {
    $factory = $this->factory
        ->state([
            'name' => 'Jane Doe',
        ]);

    expect($factory->create())->toBe(['name' => 'Jane Doe']);

});

it('overrides the definition with the attributes and the array on creates method', function () {
    $data = $this->factory
        ->state([
            'name' => 'Jane Doe',
        ])
        ->create(['name' => 'Elon Musk']);

    expect($data)->toBe(['name' => 'Elon Musk']);

});
