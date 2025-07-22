<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits;

use BitMx\DataEntities\Stores\ParameterStore;

trait HasParameters
{
    protected ParameterStore $parameters;

    public function parameters(): ParameterStore
    {
        return $this->parameters ??= new ParameterStore($this->defaultParameters());
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function defaultParameters(): array
    {
        return [];
    }
}
