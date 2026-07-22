<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

trait HasQueryTimeout
{
    /**
     * Query timeout in seconds. Null keeps the connection default.
     */
    public function queryTimeout(): ?int
    {
        return null;
    }
}
