<?php

use BitMx\DataEntities\Attributes\MapTo;
use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;
use Illuminate\Support\Collection;

afterEach(function () {
    DataEntity::resetMock();
});

class PostDto
{
    public function __construct(
        public int $id,
        public string $title,
    ) {}
}

it('maps response data to a DTO via MapTo attribute', function () {
    $dataEntity = new #[SingleItemResponse, MapTo(PostDto::class)] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make([
            'id' => 1,
            'title' => 'Hello',
        ]),
    ]);

    $dto = $dataEntity->execute()->dto();

    expect($dto)->toBeInstanceOf(PostDto::class)
        ->and($dto->id)->toBe(1)
        ->and($dto->title)->toBe('Hello');
});

it('maps a collection of rows to DTOs when MapTo receives a collection class', function () {
    $dataEntity = new #[MapTo(PostDto::class, Collection::class)] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make([
            ['id' => 1, 'title' => 'First'],
            ['id' => 2, 'title' => 'Second'],
        ]),
    ]);

    $dtos = $dataEntity->execute()->dto();

    expect($dtos)->toBeInstanceOf(Collection::class)
        ->and($dtos)->toHaveCount(2)
        ->and($dtos->first())->toBeInstanceOf(PostDto::class)
        ->and($dtos->first()->id)->toBe(1)
        ->and($dtos->last()->title)->toBe('Second');
});

it('maps an empty collection when MapTo receives a collection class', function () {
    $dataEntity = new #[MapTo(PostDto::class, Collection::class)] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make([]),
    ]);

    $dtos = $dataEntity->execute()->dto();

    expect($dtos)->toBeInstanceOf(Collection::class)
        ->and($dtos)->toBeEmpty();
});

it('wraps a single-item response in a collection when MapTo receives a collection class', function () {
    $dataEntity = new #[SingleItemResponse, MapTo(PostDto::class, Collection::class)] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make([
            'id' => 1,
            'title' => 'Hello',
        ]),
    ]);

    $dtos = $dataEntity->execute()->dto();

    expect($dtos)->toBeInstanceOf(Collection::class)
        ->and($dtos)->toHaveCount(1)
        ->and($dtos->first())->toBeInstanceOf(PostDto::class)
        ->and($dtos->first()->title)->toBe('Hello');
});

it('prefers createDtoFromResponse overrides over MapTo', function () {
    $dataEntity = new #[SingleItemResponse, MapTo(PostDto::class)] class extends DataEntity
    {
        public function resolveStoreProcedure(): string
        {
            return 'sp_test';
        }

        public function createDtoFromResponse($response): mixed
        {
            return ['manual' => true];
        }
    };

    DataEntity::fake([
        $dataEntity::class => MockResponse::make([
            'id' => 1,
            'title' => 'Hello',
        ]),
    ]);

    expect($dataEntity->execute()->dto())->toBe(['manual' => true]);
});
