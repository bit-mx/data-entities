<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Tests\Integration;

use BitMx\DataEntities\Tests\TestCase;
use Illuminate\Support\Facades\DB;

abstract class MySqlTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('data-entities.database', 'mysql');
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'data_entities'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
    }

    protected function setUp(): void
    {
        if (env('DATA_ENTITIES_INTEGRATION_MYSQL') !== '1') {
            $this->markTestSkipped('Set DATA_ENTITIES_INTEGRATION_MYSQL=1 to run MySQL integration tests.');
        }

        parent::setUp();

        $this->createStoredProcedures();
    }

    protected function createStoredProcedures(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_list_posts');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_create_post');

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_list_posts(IN p_author_id INT)
            BEGIN
                SELECT p_author_id AS author_id, 'Hello' AS title
                UNION ALL
                SELECT p_author_id AS author_id, 'World' AS title;
            END
        SQL);

        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE sp_create_post(IN p_title VARCHAR(100), OUT p_new_id INT)
            BEGIN
                SET p_new_id = 42;
                SELECT p_title AS title;
            END
        SQL);
    }
}
