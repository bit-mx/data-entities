<?php

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Executers\Executer;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;

/**
 * @mixin DataEntity
 */
trait ExecutesQuery
{
    public function execute(): Response
    {
        $executer = new Executer($this->createPendingQuery());

        $reponse = $executer->exec();

        return $reponse;
    }

    public function createPendingQuery(): PendingQuery
    {
        $pendingQuery = new PendingQuery($this);

        return $pendingQuery;
    }
}
