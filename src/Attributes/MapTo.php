<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class MapTo
{
    /**
     * @param  class-string  $class
     */
    public function __construct(
        public readonly string $class,
    ) {}
}
