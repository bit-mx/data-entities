<?php

namespace BitMx\DataEntities;

use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Traits\DataEntity\Bootable;
use BitMx\DataEntities\Traits\DataEntity\ExecutesQuery;
use BitMx\DataEntities\Traits\DataEntity\HasConnection;
use BitMx\DataEntities\Traits\DataEntity\HasFakeableResponse;
use BitMx\DataEntities\Traits\HasParameters;

abstract class DataEntity
{
    use Bootable;
    use ExecutesQuery;
    use HasConnection;
    use HasFakeableResponse;
    use HasParameters;

    protected Method $method = Method::SELECT;

    abstract public function resolveStoreProcedure(): string;

    public function createDtoFromResponse(Response $response): mixed
    {
        return null;
    }

    public function getMethod(): Method
    {
        if (! isset($this->method)) {
            throw new \LogicException('Your data entity is missing a method. You must add a method property like [protected Method $method = Method::SELECT]');
        }

        return $this->method;
    }
}
