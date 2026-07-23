<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Commands;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Introspection\ProcedureIntrospectorResolver;
use BitMx\DataEntities\Introspection\ProcedureParameter;
use BitMx\DataEntities\Support\DataEntityFinder;
use Illuminate\Console\Command;
use ReflectionClass;

class CheckDataEntities extends Command
{
    protected $signature = 'data-entities:check {--path=app/DataEntities : Path to scan for Data Entity classes}';

    protected $description = 'Check Data Entities against stored procedure signatures in the database';

    public function handle(DataEntityFinder $finder, ProcedureIntrospectorResolver $resolver): int
    {
        $pathOption = $this->option('path');
        $path = is_string($pathOption) ? $pathOption : 'app/DataEntities';

        $entities = $finder->find($path);

        if ($entities === []) {
            $this->components->warn('No Data Entity classes were found.');

            return self::SUCCESS;
        }

        $failures = 0;

        foreach ($entities as $class) {
            $reflection = new ReflectionClass($class);
            /** @var DataEntity $entity */
            $entity = $reflection->newInstanceWithoutConstructor();
            $procedure = $entity->resolveStoreProcedure();
            $connection = $entity->resolveDatabaseConnection();
            $introspector = $resolver->resolve($connection);

            if (! $introspector->procedureExists($procedure)) {
                $this->components->error(sprintf('%s → procedure [%s] not found on [%s]', $class, $procedure, $connection));
                $failures++;

                continue;
            }

            if (($reflection->getConstructor()?->getNumberOfRequiredParameters() ?? 0) > 0) {
                $this->components->info(sprintf('%s → procedure exists (%s on %s); skipped parameter drift (constructor required)', $class, $procedure, $connection));

                continue;
            }

            /** @var DataEntity $instantiated */
            $instantiated = $reflection->newInstance();
            $dbParameters = collect($introspector->parameters($procedure));
            $entityParameters = $this->stringKeys($instantiated->parameters()->all());
            $entityOutputs = $this->stringKeys($instantiated->outputParameters()->all());

            $dbInputs = $dbParameters
                ->filter(fn (ProcedureParameter $parameter): bool => $parameter->isInput)
                ->map(fn (ProcedureParameter $parameter): string => $parameter->name)
                ->values()
                ->all();
            $dbOutputs = $dbParameters
                ->filter(fn (ProcedureParameter $parameter): bool => $parameter->isOutput)
                ->map(fn (ProcedureParameter $parameter): string => $parameter->name)
                ->values()
                ->all();

            $missingInputs = array_values(array_diff($dbInputs, $entityParameters));
            $extraInputs = array_values(array_diff($entityParameters, $dbInputs));
            $missingOutputs = array_values(array_diff($dbOutputs, $entityOutputs));
            $extraOutputs = array_values(array_diff($entityOutputs, $dbOutputs));

            if ($missingInputs === [] && $extraInputs === [] && $missingOutputs === [] && $extraOutputs === []) {
                $this->components->info(sprintf('%s → OK (%s on %s)', $class, $procedure, $connection));

                continue;
            }

            $failures++;
            $this->components->error(sprintf('%s → drift detected for [%s] on [%s]', $class, $procedure, $connection));

            if ($missingInputs !== []) {
                $this->line('  missing inputs: '.implode(', ', $missingInputs));
            }

            if ($extraInputs !== []) {
                $this->line('  extra inputs: '.implode(', ', $extraInputs));
            }

            if ($missingOutputs !== []) {
                $this->line('  missing outputs: '.implode(', ', $missingOutputs));
            }

            if ($extraOutputs !== []) {
                $this->line('  extra outputs: '.implode(', ', $extraOutputs));
            }
        }

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  array<array-key, mixed>  $items
     * @return list<string>
     */
    protected function stringKeys(array $items): array
    {
        $keys = [];

        foreach (array_keys($items) as $key) {
            if (is_string($key)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }
}
