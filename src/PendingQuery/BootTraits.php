<?php

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class BootTraits
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $dataEntity = $pendingQuery->getDataEntity();

        $taits = $this->classUses($dataEntity);

        $this->bootTraits($taits, $pendingQuery);

        return $pendingQuery;
    }

    /**
     * @param  object|class-string  $class
     * @return array<class-string>
     */
    protected function classUses(object|string $class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        /**
         * @var array<class-string, class-string>|false $classParents
         */
        $classParents = class_parents($class);

        if ($classParents === false) {
            $classParents = [];
        }

        foreach (array_reverse($classParents) + [$class => $class] as $class) {
            $results += $this->getTraitUsesRecursive($class);
        }

        return array_unique($results);
    }

    /**
     * @param  class-string  $trait
     * @return array<class-string, class-string>
     */
    protected function getTraitUsesRecursive(string $trait): array
    {
        /** @var array<class-string, class-string> $traits */
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += static::getTraitUsesRecursive($trait);
        }

        return $traits;
    }

    /**
     * @param  array<class-string>  $traits
     */
    protected function bootTraits(array $traits, PendingQuery $pendingQuery): void
    {
        foreach ($traits as $trait) {
            $this->bootTrait($trait, $pendingQuery);
        }
    }

    /**
     * @param  class-string|object  $trait
     */
    protected function bootTrait(string|object $trait, PendingQuery $pendingQuery): void
    {
        $traitReflection = new \ReflectionClass($trait);

        $bootMethodName = 'boot'.$traitReflection->getShortName();

        $dataEntity = $pendingQuery->getDataEntity();

        if (! method_exists($dataEntity, $bootMethodName)) {
            return;
        }

        $dataEntity->{$bootMethodName}($pendingQuery);
    }
}
