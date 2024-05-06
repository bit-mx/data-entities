<?php

namespace BitMx\DataEntities\Parameters;

use BitMx\DataEntities\Contracts\DataStore;

class ParametersProcessor
{
    /**
     * @return array<array-key, mixed>
     */
    public function process(DataStore $store): array
    {
        $parameters = collect($store->all());

        $newParameters = $parameters->mapWithKeys(function (mixed $value, string $key) {
            return [
                $key => $this->processParameterValue($value),
            ];
        });

        return $newParameters->all();
    }

    protected function processParameterValue(mixed $value): string|int
    {
        return match (true) {
            $value instanceof \DateTime => $value->format('Y-m-d H:i:s'),
            is_bool($value) => $value ? 1 : 0,
            $value instanceof \BackedEnum => $value->value,
            $value instanceof \Stringable => (string) $value,
            default => $value,
        };
    }
}
