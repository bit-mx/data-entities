<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Commands;

use BitMx\DataEntities\Generators\DataEntityGenerator;
use BitMx\DataEntities\Introspection\ProcedureIntrospectorResolver;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeDataEntity extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:data-entity {name} {--from-procedure=} {--connection=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new data entity class';

    /**
     * @var string
     */
    protected $type = 'Data entity';

    protected string $namespace = 'App\DataEntities';

    #[\Override]
    public function handle(): ?bool
    {
        $fromProcedure = $this->option('from-procedure');

        if (! is_string($fromProcedure) || $fromProcedure === '') {
            return parent::handle();
        }

        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);

        if ((! $this->hasOption('force') || ! $this->option('force')) && $this->alreadyExists($this->getNameInput())) {
            $this->components->error($this->type.' already exists.');

            return false;
        }

        $connectionOption = $this->option('connection');
        $connection = is_string($connectionOption) && $connectionOption !== ''
            ? $connectionOption
            : (string) config('data-entities.database', 'sqlsrv');

        $introspector = (new ProcedureIntrospectorResolver)->resolve($connection);

        if (! $introspector->procedureExists($fromProcedure)) {
            $this->components->error(sprintf('Stored procedure [%s] was not found on connection [%s].', $fromProcedure, $connection));

            return false;
        }

        $this->makeDirectory($path);

        $class = class_basename($name);
        $namespace = Str::beforeLast($name, '\\');
        $contents = (new DataEntityGenerator)->generate(
            namespace: $namespace,
            class: $class,
            procedure: $fromProcedure,
            parameters: $introspector->parameters($fromProcedure),
        );

        $this->files->put($path, $contents);
        $this->components->info(sprintf('%s [%s] created successfully from [%s].', $this->type, $path, $fromProcedure));

        return true;
    }

    #[\Override]
    protected function getStub(): string
    {
        return $this->getStubPath();
    }

    public function getStubPath(): string
    {
        return __DIR__.'/../../stubs/data-entity.stub';
    }

    /**
     * @param  string  $rootNamespace
     */
    #[\Override]
    protected function getDefaultNamespace(mixed $rootNamespace): string
    {
        return $this->namespace;
    }
}
