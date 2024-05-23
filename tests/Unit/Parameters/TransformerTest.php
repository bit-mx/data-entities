<?php

use BitMx\DataEntities\Parameters\Transformer;
use Carbon\Carbon;

it('cast a int value', function () {
    $transformer = Transformer::make(1, 'key', [], ['key' => 1]);

    $result = $transformer->transform();

    expect($result)->toBeInt()
        ->toBe(1);
});

it('cast a bool value', function () {
    $transformer = Transformer::make(true, 'key', [], ['key' => 1]);

    $result = $transformer->transform();

    $transformer2 = Transformer::make(false, 'key', [], ['key' => 1]);

    $result2 = $transformer2->transform();

    expect($result)->toBeInt()
        ->toBe(1);

    expect($result2)->toBeInt()
        ->toBe(0);
});

it('cast a string value', function () {

    $transformer = Transformer::make('value', 'key', [], ['key' => 1]);

    $result = $transformer->transform();

    expect($result)->toBeString()
        ->toBe('value');
});

it('cast a float value', function () {

    $transformer = Transformer::make(1.1, 'key', [], ['key' => 1]);

    $result = $transformer->transform();

    expect($result)->toBeFloat()
        ->toBe(1.1);
});

it('cast a null value', function () {

    $transformer = Transformer::make(null, 'key', [], ['key' => 1]);

    $result = $transformer->transform();

    expect($result)->toBeNull();
});

it('cast a value with no cast', function () {

    $transformer = Transformer::make('1', 'key', [], ['key' => 1]);

    $result = $transformer->transform();

    expect($result)->toBeString()
        ->toBe('1');
});

it('cast a value with a cast', function () {

    $transformer = Transformer::make('1', 'key', ['key' => 'int'], ['key' => 1]);

    $result = $transformer->transform();

    expect($result)->toBeInt()
        ->toBe(1);
});

it('cast a DateTime value', function () {
    $transformer = Transformer::make(Carbon::parse('2000-01-01 12:30'), 'key', ['key' => 'datetime'], []);

    $result = $transformer->transform();

    expect($result)->toBeString()
        ->toBe('2000-01-01 12:30:00');
});

it('cast a DateTime value with format', function () {
    $transformer = Transformer::make(Carbon::parse('2000-01-01 12:30'), 'key', ['key' => 'datetime:Y-m-d H:i'], []);

    $result = $transformer->transform();

    expect($result)->toBeString()
        ->toBe('2000-01-01 12:30');
});

it('cast a Date value', function () {
    $transformer = Transformer::make(Carbon::parse('2000-01-01 18:00'), 'key', ['key' => 'date'], []);

    $result = $transformer->transform();

    expect($result)->toBeString()
        ->toBe('2000-01-01');
});

it('cast a array value', function () {
    $transformer = Transformer::make([1, 2, 3, 4], 'json_key', ['json_key' => 'json'], []);

    $result = $transformer->transform();

    expect($result)->toBeString()
        ->toBe('[1,2,3,4]');
});

it('cast a array value with numbers', function () {
    $transformer = Transformer::make(['1', '2', '3', '4'], 'json_key', ['json_key' => 'json:JSON_NUMERIC_CHECK'], []);

    $result = $transformer->transform();

    expect($result)->toBeString()
        ->toBe('[1,2,3,4]');
});

it('cast a value of DateTimeInterface when no cast is set', function () {
    $transformer = Transformer::make(Carbon::parse('2000-01-01 12:30'), 'key', [], []);

    $result = $transformer->transform();

    expect($result)->toBeString()
        ->toBe('2000-01-01 12:30:00');
});

it('cast a Backed enum', function () {
    enum TestEnum: int
    {
        case TEST = 1;
    }

    $transformer = Transformer::make(TestEnum::TEST, 'key', [], []);

    $result = $transformer->transform();

    expect($result)->toBeInt()
        ->toBe(1);
});
