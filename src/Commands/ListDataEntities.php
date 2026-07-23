<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Commands;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Support\DataEntityFinder;
use Illuminate\Console\Command;

class ListDataEntities extends Command
{
    protected $signature = 'data-entities:list {--path=app/DataEntities : Path to scan for Data Entity classes}';

    protected $description = 'List Data Entities and their stored procedures';

    public function handle(DataEntityFinder $finder): int
    {
        $path = $this->option('path');
        assert(is_string($path));

        $entities = $finder->find($path);

        if ($entities === []) {
            $this->components->warn('No Data Entity classes were found.');

            return self::SUCCESS;
        }

        $rows = [];

        foreach ($entities as $class) {
            /** @var DataEntity $entity */
            $entity = (new \ReflectionClass($class))->newInstanceWithoutConstructor();

            $rows[] = [
                $class,
                $entity->resolveStoreProcedure(),
                $entity->resolveDatabaseConnection(),
            ];
        }

        $this->table(['Entity', 'Stored procedure', 'Connection'], $rows);

        return self::SUCCESS;
    }
}
