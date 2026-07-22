<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Generators;

use BitMx\DataEntities\Introspection\ProcedureParameter;

class DataEntityGenerator
{
    /**
     * @param  list<ProcedureParameter>  $parameters
     */
    public function generate(
        string $namespace,
        string $class,
        string $procedure,
        array $parameters,
    ): string {
        $inputs = array_values(array_filter($parameters, fn (ProcedureParameter $parameter) => $parameter->isInput));
        $outputs = array_values(array_filter($parameters, fn (ProcedureParameter $parameter) => $parameter->isOutput));

        $constructor = $this->buildConstructor($inputs);
        $defaultParameters = $this->buildDefaultParameters($inputs);
        $mutators = $this->buildMutators($inputs);
        $outputParameters = $this->buildOutputParameters($outputs);

        $body = <<<PHP
<?php

namespace {$namespace};

use BitMx\\DataEntities\\DataEntity;

class {$class} extends DataEntity
{
{$constructor}
    #[\\Override]
    public function resolveStoreProcedure(): string
    {
        return '{$procedure}';
    }

{$defaultParameters}
{$mutators}
{$outputParameters}
}

PHP;

        return $body;
    }

    /**
     * @param  list<ProcedureParameter>  $inputs
     */
    protected function buildConstructor(array $inputs): string
    {
        if ($inputs === []) {
            return '';
        }

        $params = collect($inputs)
            ->map(fn (ProcedureParameter $parameter) => sprintf(
                '        protected %s $%s,',
                $parameter->phpType(),
                $this->toCamelCase($parameter->name),
            ))
            ->implode("\n");

        return <<<PHP
    public function __construct(
{$params}
    ) {
    }


PHP;
    }

    /**
     * @param  list<ProcedureParameter>  $inputs
     */
    protected function buildDefaultParameters(array $inputs): string
    {
        $lines = collect($inputs)
            ->map(fn (ProcedureParameter $parameter) => sprintf(
                "            '%s' => \$this->%s,",
                $parameter->name,
                $this->toCamelCase($parameter->name),
            ))
            ->implode("\n");

        if ($lines === '') {
            $lines = '            //';
        }

        return <<<PHP
    #[\\Override]
    protected function defaultParameters(): array
    {
        return [
{$lines}
        ];
    }

PHP;
    }

    /**
     * @param  list<ProcedureParameter>  $inputs
     */
    protected function buildMutators(array $inputs): string
    {
        $lines = collect($inputs)
            ->map(fn (ProcedureParameter $parameter) => [$parameter->name, $parameter->suggestedMutator()])
            ->filter(fn (array $pair) => $pair[1] !== null)
            ->map(fn (array $pair) => sprintf("            '%s' => '%s',", $pair[0], $pair[1]))
            ->implode("\n");

        if ($lines === '') {
            return '';
        }

        return <<<PHP

    #[\\Override]
    protected function mutators(): array
    {
        return [
{$lines}
        ];
    }

PHP;
    }

    /**
     * @param  list<ProcedureParameter>  $outputs
     */
    protected function buildOutputParameters(array $outputs): string
    {
        if ($outputs === []) {
            return '';
        }

        $lines = collect($outputs)
            ->map(fn (ProcedureParameter $parameter) => sprintf(
                "            '%s' => '%s',",
                $parameter->name,
                $parameter->sqlType,
            ))
            ->implode("\n");

        return <<<PHP

    #[\\Override]
    protected function defaultOutputParameters(): array
    {
        return [
{$lines}
        ];
    }

PHP;
    }

    protected function toCamelCase(string $name): string
    {
        $name = str_replace(['-', '.'], '_', $name);

        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
    }
}
