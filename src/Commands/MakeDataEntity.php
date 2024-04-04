<?php

namespace BitMx\DataEntities\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeDataEntity extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:data-entity {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new data entity class';

    protected string $namespace = 'App\DataEntities';

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
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     */
    #[\Override]
    protected function getDefaultNamespace(mixed $rootNamespace): string
    {
        return $this->namespace;
    }
}
