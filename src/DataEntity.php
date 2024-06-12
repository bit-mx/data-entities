<?php

namespace BitMx\DataEntities;

use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Traits\DataEntity\Assertable;
use BitMx\DataEntities\Traits\DataEntity\Bootable;
use BitMx\DataEntities\Traits\DataEntity\ExecutesQuery;
use BitMx\DataEntities\Traits\DataEntity\HasAccessors;
use BitMx\DataEntities\Traits\DataEntity\HasAlias;
use BitMx\DataEntities\Traits\DataEntity\HasConnection;
use BitMx\DataEntities\Traits\DataEntity\HasDumpableQuery;
use BitMx\DataEntities\Traits\DataEntity\HasFakeableResponse;
use BitMx\DataEntities\Traits\DataEntity\HasMiddleware;
use BitMx\DataEntities\Traits\DataEntity\HasMutators;
use BitMx\DataEntities\Traits\HasOutputParameters;
use BitMx\DataEntities\Traits\HasParameters;
use BitMx\DataEntities\Traits\HasQueryStatements;

abstract class DataEntity
{
    use Assertable;
    use Bootable;
    use ExecutesQuery;
    use HasAccessors;
    use HasAlias;
    use HasConnection;
    use HasDumpableQuery;
    use HasFakeableResponse;
    use HasMiddleware;
    use HasMutators;
    use HasOutputParameters;
    use HasParameters;
    use HasQueryStatements;

    protected ?Method $method = null;

    protected ?ResponseType $responseType = null;

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

    public function getResponseType(): ResponseType
    {
        if (! isset($this->responseType)) {
            throw new \LogicException('Your data entity is missing a response type. You must add a response type property like [protected ResponseTypeEnum $responseType = ResponseTypeEnum::COLLECTION]');
        }

        return $this->responseType;
    }
}
