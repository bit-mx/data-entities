<?php

use BitMx\DataEntities\Stores\ArrayStore;
use BitMx\DataEntities\Stores\ParameterStore;
use BitMx\DataEntities\Stores\StatementStore;
use Illuminate\Support\Collection;

it('creates an ArrayStore with initial values', function () {
    $store = new ArrayStore(['a' => 1, 'b' => 2]);

    expect($store->all())->toBe(['a' => 1, 'b' => 2])
        ->and($store->get('a'))->toBe(1)
        ->and($store->get('missing', 'fallback'))->toBe('fallback')
        ->and($store->count())->toBe(2)
        ->and($store->isEmpty())->toBeFalse()
        ->and($store->isNotEmpty())->toBeTrue();
});

it('adds, merges and sets values on ArrayStore', function () {
    $store = new ArrayStore;

    $store->add('name', 'Ada');
    $store->add(['age' => 36]);
    $store->merge(['city' => 'London']);

    expect($store->all())->toBe([
        'name' => 'Ada',
        'age' => 36,
        'city' => 'London',
    ]);

    $store->set(['reset' => true]);

    expect($store->all())->toBe(['reset' => true]);
});

it('converts ArrayStore to collection, array and object', function () {
    $store = new ArrayStore(['a' => 1, 'b' => 2]);

    expect($store->all())->toBe(['a' => 1, 'b' => 2])
        ->and($store->toArray())->toBe(['a' => 1, 'b' => 2])
        ->and($store->toCollection())->toBeInstanceOf(Collection::class)
        ->and($store->toObject())->toEqual((object) ['a' => 1, 'b' => 2]);
});

it('supports ArrayAccess on ArrayStore', function () {
    $store = new ArrayStore;

    $store['id'] = 10;

    expect(isset($store['id']))->toBeTrue()
        ->and($store['id'])->toBe(10);

    unset($store['id']);

    expect(isset($store['id']))->toBeFalse();
});

it('exposes keys from ParameterStore', function () {
    $store = new ParameterStore(['post_id' => 1, 'status' => 'active']);

    expect($store->keys()->all())->toBe(['post_id', 'status']);
});

it('stores statements in StatementStore', function () {
    $store = new StatementStore;

    $store->add(null, 'sp_test');
    $store->add(null, 'sp_other');

    expect($store->all())->toBe(['sp_test', 'sp_other'])
        ->and($store->toCollection()->join('; '))->toBe('sp_test; sp_other');
});
