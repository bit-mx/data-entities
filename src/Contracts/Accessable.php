<?php

namespace BitMx\DataEntities\Contracts;

interface Accessable
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function get(string $key, mixed $value, array $data): mixed;
}
