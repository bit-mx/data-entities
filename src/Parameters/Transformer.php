<?php

namespace BitMx\DataEntities\Parameters;

use BitMx\DataEntities\Contracts\Mutable;
use BitMx\DataEntities\Exceptions\InvalidMutatorException;
use BitMx\DataEntities\Exceptions\InvalidParameterValueException;
use Illuminate\Support\Arr;

final class Transformer
{
    /**
     * @param  array<string, mixed>  $transformers
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(
        protected mixed $value,
        protected string $key,
        protected array $transformers = [],
        protected array $parameters = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $mutators
     * @param  array<string, mixed>  $parameters
     */
    public static function make(mixed $value, string $key, array $mutators = [], array $parameters = []): static
    {
        return new self($value, $key, $mutators, $parameters);
    }

    public function transform(): string|int|bool|float|null
    {
        if (is_null($this->value)) {
            return null;
        }

        if (! array_key_exists($this->key, $this->transformers) && ! is_bool($this->value) && is_scalar($this->value)) {
            return $this->value;
        }

        $mutator = $this->getRule();

        $pieces = str($mutator)->explode(':', 2);

        $class = $pieces->first();

        $attributes = $pieces->count() > 1 ? explode(',', $pieces->get(1, '')) : [];

        if (array_key_exists($class, MutatorsAlias::get())) {
            $class = MutatorsAlias::get()[$class];
        }

        if (! class_exists($class)) {
            throw new InvalidMutatorException("The class {$class} does not exist");
        }

        $reflectionClass = new \ReflectionClass($class);

        if ($reflectionClass->isEnum()) {
            return $this->value->value;
        }

        if (! $reflectionClass->implementsInterface(Mutable::class)) {
            throw new InvalidMutatorException("The class {$mutator} must implement the Mutable interface");
        }

        /**
         * @var Mutable $transformer
         */
        $transformer = new $class(...$attributes);

        return $transformer->transform($this->key, $this->value, Arr::except($this->parameters, $this->key));
    }

    protected function getRule(): ?string
    {
        if (! array_key_exists($this->key, $this->transformers)) {

            if (is_bool($this->value)) {
                return 'bool';
            }

            if ($this->value instanceof \BackedEnum) {
                return $this->value::class;
            }

            if ($this->value instanceof \DateTimeInterface) {
                return 'datetime:Y-m-d H:i:s';
            }

            throw new InvalidParameterValueException("The value of the parameter {$this->key} must be a scalar value");
        }

        return $this->transformers[$this->key];
    }
}
