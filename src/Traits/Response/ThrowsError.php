<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\Response;

use BitMx\DataEntities\Responses\Response;

/**
 * @mixin Response
 */
trait ThrowsError
{
    public function getError(): ?string
    {
        return $this->senderException?->getMessage();
    }

    public function throw(): void
    {
        if ($this->senderException === null) {
            return;
        }

        throw $this->senderException;
    }
}
