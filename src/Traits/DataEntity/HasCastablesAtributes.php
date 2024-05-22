<?php

namespace BitMx\DataEntities\Traits\DataEntity;

trait HasCastablesAtributes
{
    /**
     * @var array<string, string>
     */
    private array $casts = [];

    /**
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        return $this->mergeCasts();
    }

    /**
     * @param  array<string, string>  $casts
     */
    public function setCasts(array $casts): void
    {
        $this->casts = $casts;
    }

    /**
     * @return array<string, string>
     */
    private function mergeCasts(): array
    {
        return array_merge($this->casts(), $this->casts);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }
}
