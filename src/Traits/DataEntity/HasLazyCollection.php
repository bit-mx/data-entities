<?php

namespace BitMx\DataEntities\Traits\DataEntity;

trait HasLazyCollection
{
    protected bool $useLazyCollection = false;

    public function useLazyCollection(): bool
    {
        return $this->useLazyCollection;
    }

    public function enableUseLazyCollection(): void
    {
        $this->useLazyCollection = true;
    }
}
