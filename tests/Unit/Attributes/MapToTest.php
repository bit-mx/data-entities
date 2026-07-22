<?php

use BitMx\DataEntities\Attributes\MapTo;
use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;

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
