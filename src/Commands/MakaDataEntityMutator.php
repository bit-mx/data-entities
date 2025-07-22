<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Commands;

use Illuminate\Console\GeneratorCommand;

class MakaDataEntityMutator extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:data-entity-mutator {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new custom mutator class';

    protected string $namespace = 'App\DataEntityMutators';

    #[\Override]
    protected function getStub(): string
    {
        return $this->getStubPath();
    }

    public function getStubPath(): string
    {
        return __DIR__.'/../../stubs/mutable.stub';
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
