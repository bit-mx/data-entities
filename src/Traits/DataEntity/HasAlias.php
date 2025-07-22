<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Traits\DataEntity;

trait HasAlias
{
    /**
     * @var array<string, string>
     */
    private array $alias = [];

    /**
     * @return array<string, string>
     */
    public function getalias(): array
    {
        return $this->mergeAlias();
    }

    /**
     * @param  array<string, string>  $alias
     */
    public function setAlias(array $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return array<string, string>
     */
    private function mergeAlias(): array
    {
        return array_merge($this->alias(), $this->alias);
    }

    /**
     * @return array<string, string>
     */
    protected function alias(): array
    {
        return [];
    }
}
