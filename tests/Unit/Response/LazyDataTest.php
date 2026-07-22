<?php

use BitMx\DataEntities\Attributes\UseLazyQuery;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Tests\Helpers\UppercaseAccessor;
use Illuminate\Support\LazyCollection;

it('keeps lazy() re-iterable with remember semantics', function () {
    $emissions = 0;

    $dataEntity = new #[UseLazyQuery] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function accessors(): array
        {
            return [
                'title' => UppercaseAccessor::class,
            ];
        }
    };

    $raw = LazyCollection::make(function () use (&$emissions) {
        $emissions++;
        yield ['title' => 'hello'];
        yield ['title' => 'world'];
    });

    $response = new Response(new PendingQuery($dataEntity), rawLazyData: $raw);

    expect($response->lazy()->all())->toBe([
        ['title' => 'HELLO'],
        ['title' => 'WORLD'],
    ])->and($response->lazy()->all())->toBe([
        ['title' => 'HELLO'],
        ['title' => 'WORLD'],
    ])->and($emissions)->toBe(1);
});

it('streams without remembering rows and rejects a second iteration', function () {
    $dataEntity = new #[UseLazyQuery] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $raw = LazyCollection::make(function () {
        yield ['id' => 1];
        yield ['id' => 2];
    });

    $response = new Response(new PendingQuery($dataEntity), rawLazyData: $raw);
    $stream = $response->stream();

    expect($stream->all())->toBe([
        ['id' => 1],
        ['id' => 2],
    ]);

    $stream->all();
})->throws(RuntimeException::class, 'Lazy stream has already been consumed');

it('exposes stream() through lazy(remember: false)', function () {
    $dataEntity = new #[UseLazyQuery] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $raw = LazyCollection::make(function () {
        yield ['id' => 1];
    });

    $response = new Response(new PendingQuery($dataEntity), rawLazyData: $raw);

    expect($response->lazy(remember: false)->all())->toBe([['id' => 1]]);
});
