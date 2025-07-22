<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeDataEntityFactory extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:data-entity-factory {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new data entity factory class';

    protected string $namespace = 'Test\RequestFactories';

    #[\Override]
    protected function getStub(): string
    {
        return $this->getStubPath();
    }

    public function getStubPath(): string
    {
        return __DIR__.'/../../stubs/data-entity-factory.stub';
    }

    /**
     * @param  string  $name
     */
    protected function getPath(mixed $name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return base_path('tests').str_replace('\\', '/', $name).'.php';
    }

    /**
     * Get the root namespace for the class.
     */
    protected function rootNamespace(): string
    {
        return 'Tests';
    }

    /**
     * @param  string  $rootNamespace
     */
    protected function getDefaultNamespace(mixed $rootNamespace): string
    {
        return $rootNamespace.'\DataEntityFactories';
    }
}
