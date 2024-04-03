<?php

namespace BitMx\DataEntities\Executers;

use BitMx\DataEntities\Contracts\ExecuterContract;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;

class Executer
{
    public function __construct(
        protected readonly PendingQuery $pendingQuery,
    ) {
    }

    public function exec(): Response
    {
        $executer = $this->getExecuter();

        return $executer->execute();
    }

    protected function getExecuter(): ExecuterContract
    {
        return ExecuterFactory::make($this->pendingQuery->getDataEntity()->getMethod(), $this->pendingQuery)
            ->create();
    }
}
