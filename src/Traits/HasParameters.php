<?php

namespace BitMx\DataEntities\Traits;

use BitMx\DataEntities\Repositories\ParameterRepository;

trait HasParameters
{
    public ParameterRepository $parameters;

    public function parameters(): ParameterRepository
    {
        return $this->parameters ??= new ParameterRepository($this->defaultParameters());
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function defaultParameters(): array
    {
        return [];
    }
}
