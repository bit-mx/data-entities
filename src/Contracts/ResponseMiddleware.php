<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Contracts;

use BitMx\DataEntities\Responses\Response;

interface ResponseMiddleware
{
    /**
     * @return Response|void
     */
    public function __invoke(Response $response): mixed;
}
