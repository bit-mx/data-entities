<?php

namespace BitMx\DataEntities\Traits\PendingQuery;

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\PendingQuery;

/**
 * @mixin PendingQuery
 */
trait HasResponseType
{
    private ResponseType $responseType;

    public function getResponseType(): ResponseType
    {
        return $this->responseType ??= $this->inferResponseType();
    }

    private function inferResponseType(): ResponseType
    {
        $dataEntity = $this->getDataEntity();

        $reflection = new \ReflectionClass($dataEntity);

        $attributes = $reflection->getAttributes(SingleItemResponse::class);

        return ! empty($attributes)
            ? ResponseType::SINGLE
            : ResponseType::COLLECTION;
    }
}
