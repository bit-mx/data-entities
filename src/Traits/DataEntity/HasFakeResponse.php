<?php

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\Response;
use BitMx\DataEntities\Traits\Response\FakeResponse;

/**
 * @mixin DataEntity
 */
trait HasFakeResponse
{
    protected function createFakeResponse(FakeResponse $getFakeResponse): Response
    {
        return new Response(
            $this->createPendingQuery(),
            $getFakeResponse->getData(),
            $getFakeResponse->getOutput(),
            $getFakeResponse->isSuccess()
        );
    }
}
