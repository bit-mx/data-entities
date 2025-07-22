<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Contracts;

use BitMx\DataEntities\Responses\Response;

interface ProcessorContract
{
    public function handle(): Response;
}
