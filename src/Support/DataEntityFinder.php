<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Support;

use BitMx\DataEntities\DataEntity;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class DataEntityFinder
{
    /**
     * @return list<class-string<DataEntity>>
     */
    public function find(string $path): array
    {
        $absolutePath = base_path($path);

        if (! File::isDirectory($absolutePath)) {
            return [];
        }

        $entities = [];

        foreach (File::allFiles($absolutePath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $class = $this->classFromFile($file->getPathname());

            if ($class === null) {
                continue;
            }

            require_once $file->getPathname();

            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if (! $reflection->isInstantiable() || ! $reflection->isSubclassOf(DataEntity::class)) {
                continue;
            }

            /** @var class-string<DataEntity> $class */
            $entities[] = $class;
        }

        sort($entities);

        return $entities;
    }

    protected function classFromFile(string $filePath): ?string
    {
        $appPath = rtrim(app_path(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

        if (! str_starts_with($filePath, $appPath)) {
            return null;
        }

        $relative = substr($filePath, strlen($appPath), -4);

        return app()->getNamespace().str_replace(['/', '\\'], '\\', $relative);
    }
}
