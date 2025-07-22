<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Parameters;

use BitMx\DataEntities\PendingQuery;

class ParametersProcessor
{
    public function __construct(
        protected PendingQuery $pendingQuery
    ) {}

    /**
     * @return array<array-key, mixed>
     */
    public function process(): array
    {
        $parameters = collect($this->pendingQuery->parameters()->all());

        $newParameters = $parameters->mapWithKeys(fn (mixed $value, string $key) => [
            $key => $this->processParameterValue($value, $key, $parameters->all()),
        ]);

        return $newParameters->all();
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    protected function processParameterValue(mixed $value, string $key, array $parameters): string|int|bool|float|null
    {
        return Transformer::make($value, $key, $this->pendingQuery->mutators()->all(), $parameters)->transform();
    }
}
