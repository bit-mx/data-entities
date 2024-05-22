<?php

namespace BitMx\DataEntities\Contracts;

interface Castable
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function transform(string $key, mixed $value, array $parameters): mixed;
}
