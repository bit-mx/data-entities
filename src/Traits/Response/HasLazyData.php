<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\Response;

use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\LazyCollection;
use RuntimeException;

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
    protected function getLazyData(bool $remember = true): LazyCollection
    {
        $hasAccessors = $this->hasAccessors();

        $collection = LazyCollection::make(function () use ($hasAccessors) {
            foreach ($this->rawLazyData as $row) {
                yield $hasAccessors
                    ? $this->mutateSingleData((array) $row)
                    : (array) $row;
            }
        });

        return $remember ? $collection->remember() : $collection;
    }

    /**
     * Re-iterable lazy collection (rows are remembered in memory after the first pass).
     *
     * @return LazyCollection<array-key, mixed>
     */
    public function lazy(bool $remember = true): LazyCollection
    {
        if (! $remember) {
            return $this->stream();
        }

        return $this->lazyData ??= $this->getLazyData(remember: true);
    }

    /**
     * Single-pass streaming collection that does not accumulate rows in memory.
     *
     * @return LazyCollection<array-key, mixed>
     */
    public function stream(): LazyCollection
    {
        $consumed = false;
        $hasAccessors = $this->hasAccessors();

        return LazyCollection::make(function () use (&$consumed, $hasAccessors) {
            if ($consumed) {
                throw new RuntimeException(
                    'Lazy stream has already been consumed and cannot be re-iterated. Use lazy() for a re-iterable collection.'
                );
            }

            $consumed = true;

            foreach ($this->rawLazyData as $row) {
                yield $hasAccessors
                    ? $this->mutateSingleData((array) $row)
                    : (array) $row;
            }
        });
    }
}
