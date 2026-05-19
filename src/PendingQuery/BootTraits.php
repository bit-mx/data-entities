<?php

declare(strict_types=1);

namespace BitMx\DataEntities\PendingQuery;

use BitMx\DataEntities\PendingQuery;

readonly class BootTraits
{
    /**
     * @param  \Closure(PendingQuery): PendingQuery  $next
     */
    public function __invoke(PendingQuery $pendingQuery, \Closure $next): PendingQuery
    {
        $dataEntity = $pendingQuery->getDataEntity();

        /** @var array<string, string> $traits */
        $traits = $this->classUses($dataEntity);

        $this->bootTraits($traits, $pendingQuery);

        return $next($pendingQuery);
    }

    /**
     * @param  object|class-string  $class
     * @return array<string, string>
     */
    protected function classUses(object|string $class): array
    {
        return class_uses_recursive($class);
    }

    /**
     * @param  array<string, string>  $traits
     */
    protected function bootTraits(array $traits, PendingQuery $pendingQuery): void
    {
        foreach ($traits as $trait) {
            $this->bootTrait($trait, $pendingQuery);
        }
    }

    protected function bootTrait(string|object $trait, PendingQuery $pendingQuery): void
    {
        /** @var class-string $trait */
        $traitReflection = new \ReflectionClass($trait);

        $bootMethodName = 'boot'.$traitReflection->getShortName();

        $dataEntity = $pendingQuery->getDataEntity();

        if (! method_exists($dataEntity, $bootMethodName)) {
            return;
        }

        $dataEntity->{$bootMethodName}($pendingQuery);
    }
}
