<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\PendingQuery;

readonly class MergeQueryStatements
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {

        $dataEntity = $pendingQuery->getDataEntity();

        $storeProcedure = sprintf('EXEC %s ', $dataEntity->resolveStoreProcedure());

        $statements = [];

        if ($pendingQuery->getMethod() === Method::STATEMENT) {
            $statements[] = 'SET NOCOUNT ON';
        }

        $currentStatements = $dataEntity->statements()->all();

        $pendingQuery->statements()
            ->merge($statements, $currentStatements, [$storeProcedure]);

        return $pendingQuery;
    }
}
