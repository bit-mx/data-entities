<?php

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Tests\Integration\MySqlTestCase;

uses(MySqlTestCase::class);

it('executes a MySQL stored procedure and returns rows', function () {
    $dataEntity = new class(7) extends DataEntity
    {
        public function __construct(private int $authorId) {}

        public function resolveStoreProcedure(): string
        {
            return 'sp_list_posts';
        }

        protected function defaultParameters(): array
        {
            return [
                'p_author_id' => $this->authorId,
            ];
        }
    };

    $response = $dataEntity->execute();

    expect($response->success())->toBeTrue()
        ->and($response->data())->toBe([
            ['author_id' => 7, 'title' => 'Hello'],
            ['author_id' => 7, 'title' => 'World'],
        ]);
});

it('executes a MySQL stored procedure with output parameters', function () {
    $dataEntity = new #[SingleItemResponse] class('New post') extends DataEntity
    {
        public function __construct(private string $title) {}

        public function resolveStoreProcedure(): string
        {
            return 'sp_create_post';
        }

        protected function defaultParameters(): array
        {
            return [
                'p_title' => $this->title,
            ];
        }

        protected function defaultOutputParameters(): array
        {
            return [
                'p_new_id' => 'INT',
            ];
        }
    };

    $response = $dataEntity->execute();

    expect($response->success())->toBeTrue()
        ->and($response->data('title'))->toBe('New post')
        ->and($response->output('p_new_id'))->toBe(42);
});
