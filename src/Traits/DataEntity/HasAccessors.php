<?php

namespace BitMx\DataEntities\Traits\DataEntity;

trait HasAccessors
{
    /**
     * @var array<string, string>
     */
    private array $accessors = [];

    public function getAccessor(string $attribute): string
    {
        return $this->getAccessors()[$attribute];
    }

    /**
     * @return array<string, string>
     */
    public function getAccessors(): array
    {
        return $this->mergeAccessors();
    }

    /**
     * @param  array<string, string>  $accessors
     */
    public function setAccessors(array $accessors): self
    {
        $this->accessors = $accessors;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    private function mergeAccessors(): array
    {
        return array_merge($this->accessors(), $this->accessors);
    }

    /**
     * @return array<string, string>
     */
    protected function accessors(): array
    {
        return [];
    }
}
