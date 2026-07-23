<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

trait HasRequiredParameters
{
    /**
     * @return list<string>
     */
    public function requiredParameters(): array
    {
        return [];
    }
}
