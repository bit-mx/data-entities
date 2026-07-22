<?php

use BitMx\DataEntities\Accessors\AsDecimal as AccessorAsDecimal;
use BitMx\DataEntities\Accessors\AsInteger;
use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Dumpables\DumpRawProcessor;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Mutators\AsDecimal as MutatorAsDecimal;
use BitMx\DataEntities\Mutators\AsInteger as MutatorAsInteger;
use BitMx\DataEntities\Parameters\MutatorsAlias;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\Mutators\AccessorsAlias;
use BitMx\DataEntities\Responses\RecordedResponse;
use BitMx\DataEntities\Tests\Helpers\AssertablePrimaryEntity;
use BitMx\DataEntities\Tests\Helpers\AssertableSecondaryEntity;
use ReflectionMethod;

afterEach(function () {
    DataEntity::resetMock();
});

it('formats dump query parameters for raw dumping', function () {
    $dataEntity = new class extends DataEntity
    {
        protected ?Method $method = Method::SELECT;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        protected function defaultParameters(): array
        {
            return [
                'id' => 1,
                'name' => 'Ada',
                'amount' => 10.5,
                'flag' => true,
                'optional' => null,
            ];
        }
    };

    $processor = new DumpRawProcessor(new PendingQuery($dataEntity));

    $formatQuery = new ReflectionMethod($processor, 'formatQuery');
    $formatted = $formatQuery->invoke($processor);

    expect($formatted)
        ->toContain('sp_test')
        ->toContain('1')
        ->toContain("'Ada'")
        ->toContain('10.5')
        ->toContain('NULL');
});

it('formats individual dump parameters', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    $processor = new DumpRawProcessor(new PendingQuery($dataEntity));
    $method = new ReflectionMethod($processor, 'getFormattedParameter');

    expect($method->invoke($processor, null))->toBe('NULL')
        ->and($method->invoke($processor, 'hello'))->toBe("'hello'")
        ->and($method->invoke($processor, 42))->toBe(42)
        ->and($method->invoke($processor, 1.5))->toBe(1.5)
        ->and($method->invoke($processor, true))->toBeTrue();
});

it('creates a RecordedResponse from a Response', function () {
    $dataEntity = new #[SingleItemResponse] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => new MockResponse(['id' => 9], ['total' => 3]),
    ]);

    $response = $dataEntity->execute();
    $recorded = RecordedResponse::fromResponse($response);

    expect($recorded->data())->toBe(['id' => 9])
        ->and($recorded->output())->toBe(['total' => 3])
        ->and($recorded->jsonSerialize())->toBe([
            'data' => ['id' => 9],
            'output' => ['total' => 3],
        ]);
});

it('asserts executed queries through Assertable', function () {
    DataEntity::fake([
        AssertablePrimaryEntity::class => MockResponse::make(['ok' => true]),
        AssertableSecondaryEntity::class => MockResponse::make(['ok' => true]),
    ]);

    DataEntity::assertNotExecuted(AssertablePrimaryEntity::class);

    (new AssertablePrimaryEntity)->execute();
    (new AssertablePrimaryEntity)->execute();

    DataEntity::assertExecuted(AssertablePrimaryEntity::class);
    DataEntity::assertExecutedCount(AssertablePrimaryEntity::class, 2);
    DataEntity::assertNotExecuted(AssertableSecondaryEntity::class);
});

it('asserts a query was executed once', function () {
    DataEntity::fake([
        AssertablePrimaryEntity::class => MockResponse::make(['ok' => true]),
    ]);

    (new AssertablePrimaryEntity)->execute();

    DataEntity::assertExecutedOnce(AssertablePrimaryEntity::class);
});

it('resolves mutator aliases to concrete classes', function () {
    $aliases = MutatorsAlias::get();

    expect($aliases['int'])->toBe(MutatorAsInteger::class)
        ->and($aliases['float'])->toBe(MutatorAsDecimal::class)
        ->and($aliases['decimal'])->toBe(MutatorAsDecimal::class)
        ->and($aliases)->toHaveKeys(['datetime', 'date', 'bool', 'string', 'json']);
});

it('resolves accessor aliases to concrete classes', function () {
    $aliases = AccessorsAlias::get();

    expect($aliases['int'])->toBe(AsInteger::class)
        ->and($aliases['integer'])->toBe(AsInteger::class)
        ->and($aliases['float'])->toBe(AccessorAsDecimal::class)
        ->and($aliases['decimal'])->toBe(AccessorAsDecimal::class)
        ->and($aliases)->toHaveKeys([
            'string',
            'bool',
            'boolean',
            'datetime',
            'date',
            'datetime_immutable',
            'date_immutable',
            'array',
            'object',
            'collection',
        ]);
});

it('throws when AsDecimal mutator receives a non-numeric value', function () {
    (new MutatorAsDecimal)->transform('amount', 'not-a-number', []);
})->throws(InvalidArgumentException::class, 'must be a number value');

it('rounds AsDecimal mutator values with default and custom precision', function () {
    expect((new MutatorAsDecimal)->transform('amount', '1.239', []))
        ->toBe(1.24)
        ->and((new MutatorAsDecimal(4))->transform('amount', '1.23456', []))
        ->toBe(1.2346)
        ->and((new MutatorAsDecimal(0))->transform('amount', 1.9, []))
        ->toBe(2.0);
});
