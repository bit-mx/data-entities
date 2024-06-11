<?php

namespace BitMx\DataEntities\Pipelines;

use BitMx\DataEntities\Exceptions\DuplicatePipeNameException;

/**
 * @template T
 */
class Pipeline
{
    /**
     * @var array<int, Pipe>
     */
    protected array $pipes = [];

    /**
     * @param  T  $payload
     * @return T
     */
    public function process(mixed $payload): mixed
    {
        foreach ($this->pipes as $pipe) {
            $payload = $pipe->getCallable()($payload);
        }

        return $payload;
    }

    public function addPipe(callable $callable, ?string $name = null): void
    {
        if (is_string($name) && $this->pipeExists($name)) {
            throw new DuplicatePipeNameException($name);
        }

        $this->pipes[] = new Pipe($callable, $name);
    }

    protected function pipeExists(string $name): bool
    {
        return collect($this->pipes)->contains(fn (Pipe $pipe) => $pipe->getName() === $name);
    }

    /**
     * @return array<int, Pipe>
     */
    public function getPipes(): array
    {
        return $this->pipes;
    }

    /**
     * @param  array<int, Pipe>  $pipes
     */
    public function setPipes(array $pipes): void
    {
        $this->pipes = $pipes;
    }
}
