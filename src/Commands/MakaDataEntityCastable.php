<?php

namespace BitMx\DataEntities\Commands;

use Illuminate\Console\GeneratorCommand;

class MakaDataEntityCastable extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:data-entity-cast {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new custom cast class';

    protected string $namespace = 'App\DataEntityCasts';

    #[\Override]
    protected function getStub(): string
    {
        return $this->getStubPath();
    }

    public function getStubPath(): string
    {
        return __DIR__.'/../../stubs/castable.stub';
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
