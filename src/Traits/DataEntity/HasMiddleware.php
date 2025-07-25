<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

use BitMx\DataEntities\Pipelines\MiddlewarePipeline;

trait HasMiddleware
{
    protected MiddlewarePipeline $middlewarePipeline;

    public function middleware(): MiddlewarePipeline
    {
        return $this->middlewarePipeline ??= new MiddlewarePipeline;
    }
}
