<?php

namespace BitMx\DataEntities\Stores;

use BitMx\DataEntities\Contracts\DataStore;
use Illuminate\Support\Collection;

class ParameterStore extends ArrayStore implements DataStore
{
    /**
     * @return Collection<int, array-key>
     */
    public function keys(): Collection
    {
        return $this->toCollection()
            ->keys()
            ->values();
    }
}
