<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits;

use BitMx\DataEntities\Stores\ParameterStore;

trait HasOutputParameters
{
    public ParameterStore $outputParameters;

    public function outputParameters(): ParameterStore
    {
        return $this->outputParameters ??= new ParameterStore($this->defaultOutputParameters());
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function defaultOutputParameters(): array
    {
        return [];
    }
}
