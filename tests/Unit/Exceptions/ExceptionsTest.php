<?php

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Exceptions\InvalidAccessorException;
use BitMx\DataEntities\Exceptions\InvalidMutatorException;
use BitMx\DataEntities\Exceptions\InvalidParameterValueException;
use BitMx\DataEntities\Exceptions\MockResponseNotFoundException;
use BitMx\DataEntities\Exceptions\NoCacheableDataEntityException;
use BitMx\DataEntities\Parameters\Transformer;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Plugins\HasCache;
use BitMx\DataEntities\Responses\Mutators\Accessor;

afterEach(function () {
    DataEntity::resetMock();
});

it('throws InvalidAccessorException when accessor class does not exist', function () {
    Accessor::make(1, 'id', ['id' => 'NonExistentAccessorClass'], [])->transform();
})->throws(InvalidAccessorException::class, 'The class NonExistentAccessorClass does not exist');

it('throws InvalidAccessorException when accessor does not implement Accessable', function () {
    Accessor::make(1, 'id', ['id' => stdClass::class], [])->transform();
})->throws(InvalidAccessorException::class, 'must implement the Accessable interface');

it('throws InvalidMutatorException when mutator class does not exist', function () {
    Transformer::make(1, 'id', ['id' => 'NonExistentMutatorClass'], [])->transform();
})->throws(InvalidMutatorException::class, 'The class NonExistentMutatorClass does not exist');

it('throws InvalidMutatorException when mutator does not implement Mutable', function () {
    Transformer::make(1, 'id', ['id' => stdClass::class], [])->transform();
})->throws(InvalidMutatorException::class, 'must implement the Mutable interface');

it('throws InvalidParameterValueException for non-scalar values without a mutator', function () {
    Transformer::make(['nested'], 'payload', [], [])->transform();
})->throws(InvalidParameterValueException::class, 'must be a scalar value');

it('throws MockResponseNotFoundException when fake has no mock for the entity', function () {
    $dataEntity = new class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([]);

    $dataEntity->execute();
})->throws(MockResponseNotFoundException::class);

it('throws NoCacheableDataEntityException when HasCache is used without Cacheable', function () {
    $dataEntity = new class extends DataEntity
    {
        use HasCache;

        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    new PendingQuery($dataEntity);
})->throws(NoCacheableDataEntityException::class);
