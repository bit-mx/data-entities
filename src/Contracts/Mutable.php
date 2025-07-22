<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Contracts;

interface Mutable
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function transform(string $key, mixed $value, array $parameters): mixed;
}
