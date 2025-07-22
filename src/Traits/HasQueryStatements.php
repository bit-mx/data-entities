<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits;

use BitMx\DataEntities\Stores\StatementStore;

trait HasQueryStatements
{
    protected StatementStore $statements;

    public function statements(): StatementStore
    {
        return $this->statements ??= new StatementStore([]);
    }
}
