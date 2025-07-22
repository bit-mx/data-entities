<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

trait HasMutators
{
    /**
     * @var array<string, string>
     */
    private array $mutators = [];

    /**
     * @return array<string, string>
     */
    public function getMutators(): array
    {
        return $this->mergeMutators();
    }

    /**
     * @param  array<string, string>  $mutators
     */
    public function setMutators(array $mutators): void
    {
        $this->mutators = $mutators;
    }

    /**
     * @return array<string, string>
     */
    private function mergeMutators(): array
    {
        return array_merge($this->mutators(), $this->mutators);
    }

    /**
     * @return array<string, string>
     */
    protected function mutators(): array
    {
        return [];
    }
}
