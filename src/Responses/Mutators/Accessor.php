<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Responses\Mutators;

use BackedEnum;
use BitMx\DataEntities\Contracts\Accessable;
use BitMx\DataEntities\Exceptions\InvalidAccessorException;

final readonly class Accessor
{
    /**
     * @param  array<string, mixed>  $accessors
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        protected mixed $value,
        protected string $key,
        protected array $accessors = [],
        protected array $data = [],
    ) {}

    /**
     * @param  array<string, mixed>  $accessors
     * @param  array<string, mixed>  $data
     */
    public static function make(mixed $value, string $key, array $accessors = [], array $data = []): self
    {
        return new self($value, $key, $accessors, $data);
    }

    public function transform(): mixed
    {
        if (! array_key_exists($this->key, $this->accessors)) {
            return $this->value;
        }

        $class = $this->accessors[$this->key];

        if (! is_string($class) || $class === '') {
            throw new InvalidAccessorException("The accessor for parameter {$this->key} is invalid");
        }

        if (array_key_exists($class, AccessorsAlias::get())) {
            $class = AccessorsAlias::get()[$class];
        }

        if (! class_exists($class)) {
            throw new InvalidAccessorException("The class {$class} does not exist");
        }

        $reflectionClass = new \ReflectionClass($class);

        if ($reflectionClass->isEnum()) {
            if (! is_a($class, BackedEnum::class, true)) {
                throw new InvalidAccessorException("The class {$class} must be a BackedEnum");
            }

            if (! is_string($this->value) && ! is_int($this->value)) {
                return null;
            }

            return $class::tryFrom($this->value);
        }

        if (! $reflectionClass->implementsInterface(Accessable::class)) {
            throw new InvalidAccessorException("The class {$class} must implement the Accessable interface");
        }

        $accessor = new $class;

        if (! $accessor instanceof Accessable) {
            throw new InvalidAccessorException("The class {$class} must implement the Accessable interface");
        }

        return $accessor->get(
            key: $this->key,
            value: $this->value,
            data: $this->data,
        );

    }
}
