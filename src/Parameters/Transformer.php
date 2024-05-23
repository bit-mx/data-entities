<?php

namespace BitMx\DataEntities\Parameters;

use BitMx\DataEntities\Contracts\Castable;
use BitMx\DataEntities\Exceptions\InvalidCastException;
use BitMx\DataEntities\Exceptions\InvalidParameterValueException;
use Illuminate\Support\Arr;

final class Transformer
{
    /**
     * @param  array<string, mixed>  $casts
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(
        protected mixed $value,
        protected string $key,
        protected array $casts = [],
        protected array $parameters = [],
    ) {
    }

    /**
     * @param  array<string, mixed>  $casts
     * @param  array<string, mixed>  $parameters
     */
    public static function make(mixed $value, string $key, array $casts = [], array $parameters = []): static
    {
        return new self($value, $key, $casts, $parameters);
    }

    public function transform(): string|int|bool|float|null
    {
        if (is_null($this->value)) {
            return null;
        }

        if (! array_key_exists($this->key, $this->casts) && ! is_bool($this->value) && is_scalar($this->value)) {
            return $this->value;
        }

        $cast = $this->getRule();

        $pieces = str($cast)->explode(':', 2);

        $class = $pieces->first();

        $attributes = $pieces->count() > 1 ? explode(',', $pieces->get(1, '')) : [];

        if (array_key_exists($class, CastAlias::get())) {
            $class = CastAlias::get()[$class];
        }

        if (! class_exists($class)) {
            throw new InvalidCastException("The class {$class} does not exist");
        }

        $reflectionClass = new \ReflectionClass($class);

        if ($reflectionClass->isEnum()) {
            return $this->value->value;
        }

        if (! $reflectionClass->implementsInterface(Castable::class)) {
            throw new InvalidCastException("The class {$cast} must implement the Castable interface");
        }

        /**
         * @var Castable $transformer
         */
        $transformer = new $class(...$attributes);

        return $transformer->transform($this->key, $this->value, Arr::except($this->parameters, $this->key));
    }

    protected function getRule(): ?string
    {
        if (! array_key_exists($this->key, $this->casts)) {

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

        return $this->casts[$this->key];
    }
}
