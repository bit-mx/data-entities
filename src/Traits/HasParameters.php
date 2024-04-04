<?php

namespace BitMx\DataEntities\Traits;

use BitMx\DataEntities\Stores\ParameterStore;

trait HasParameters
{
    public ParameterStore $parameters;

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
