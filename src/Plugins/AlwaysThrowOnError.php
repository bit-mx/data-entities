<?php

namespace BitMx\DataEntities\Plugins;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\Response;

/**
 * @mixin DataEntity
 */
trait AlwaysThrowOnError
{
    public function bootAlwaysThrowOnError(): void
    {
        $this->middleware()->onResponse(function (Response $response) {
            $response->throw();
        });
    }
}
