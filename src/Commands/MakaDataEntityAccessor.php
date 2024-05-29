<?php

namespace BitMx\DataEntities\Commands;

use Illuminate\Console\GeneratorCommand;

class MakaDataEntityAccessor extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:data-entity-accessor {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new custom accessor class';

    protected string $namespace = 'App\DataEntityAccessors';

    #[\Override]
    protected function getStub(): string
    {
        return $this->getStubPath();
    }

    public function getStubPath(): string
    {
        return __DIR__.'/../../stubs/accessable.stub';
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
