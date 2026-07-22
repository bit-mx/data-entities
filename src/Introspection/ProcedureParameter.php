<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Introspection;

readonly class ProcedureParameter
{
    public function __construct(
        public string $name,
        public string $sqlType,
        public bool $isOutput = false,
        public bool $isInput = true,
    ) {}

    public function phpType(): string
    {
        $type = strtolower($this->sqlType);

        return match (true) {
            str_contains($type, 'int') => 'int',
            str_contains($type, 'bool'), str_contains($type, 'bit') => 'bool',
            str_contains($type, 'float'), str_contains($type, 'double'), str_contains($type, 'decimal'), str_contains($type, 'numeric'), str_contains($type, 'money'), str_contains($type, 'real') => 'float',
            default => 'string',
        };
    }

    public function suggestedMutator(): ?string
    {
        return match ($this->phpType()) {
            'bool' => 'bool',
            'int' => 'int',
            'float' => 'float',
            default => null,
        };
    }
}
