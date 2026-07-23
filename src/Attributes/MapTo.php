<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MapTo
{
    /**
     * @param  class-string  $class
     * @param  class-string|null  $collection
     */
    public function __construct(
        public readonly string $class,
        public readonly ?string $collection = null,
    ) {}
}
