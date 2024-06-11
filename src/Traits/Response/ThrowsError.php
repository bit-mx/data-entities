<?php

namespace BitMx\DataEntities\Traits\Response;

/**
 * @mixin \BitMx\DataEntities\Responses\Response
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
