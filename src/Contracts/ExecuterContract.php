<?php

namespace BitMx\DataEntities\Contracts;

use BitMx\DataEntities\Responses\Response;

interface ExecuterContract
{
    public function execute(): Response;
}
