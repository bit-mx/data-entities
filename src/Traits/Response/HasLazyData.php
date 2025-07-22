<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\Response;

use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\LazyCollection;

/**
 * @mixin Response
 */
trait HasLazyData
{
    /**
     * @var LazyCollection<array-key, mixed>
     */
    protected LazyCollection $lazyData;

    /**
     * @return LazyCollection<array-key, mixed>
     */
    protected function getLazyData(): LazyCollection
    {
        if ($this->rawLazyData->isEmpty()) {
            return LazyCollection::make()
                ->remember();
        }

        $hasAccessors = $this->hasAccessors();

        return LazyCollection::make(function () use ($hasAccessors) {
            foreach ($this->rawLazyData as $row) {
                yield $hasAccessors
                    ? $this->mutateSingleData((array) $row)
                    : (array) $row;
            }
        })
            ->remember();
    }

    /**
     * @return LazyCollection<array-key, mixed>
     */
    public function lazy(): LazyCollection
    {
        return $this->lazyData ??= $this->getLazyData();
    }
}
