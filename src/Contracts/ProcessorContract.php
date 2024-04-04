<?php

namespace BitMx\DataEntities\Contracts;

use BitMx\DataEntities\Responses\Response;

interface ProcessorContract
{
    public function handle(): Response;
}
