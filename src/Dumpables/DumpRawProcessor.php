<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Dumpables;

use BitMx\DataEntities\Parameters\ParametersProcessor;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Traits\Executer\HasQuery;
use Illuminate\Support\Str;
use Symfony\Component\VarDumper\VarDumper;

class DumpRawProcessor
{
    use HasQuery;

    public function __construct(
        protected readonly PendingQuery $pendingQuery,
    ) {}

    public function handler(): never
    {
        $query = $this->formatQuery();

        VarDumper::dump($query);

        exit(1);
    }

    protected function formatQuery(): string
    {
        $query = $this->prepareQuery();

        $keys = $this->pendingQuery->parameters()->keys()->map(function (mixed $key): string {
            if (! is_string($key)) {
                return '';
            }

            return sprintf(':%s', $key);
        });

        $parameters = (new ParametersProcessor($this->pendingQuery))->process();

        $formattedParameters = collect($parameters)->mapWithKeys(function (mixed $value, string $key): array {
            if (! is_scalar($value) && ! is_null($value)) {
                $formatted = $this->getFormattedParameter(null);
            } else {
                $formatted = $this->getFormattedParameter($value);
            }

            if (is_string($formatted)) {
                return [$key => $formatted];
            }

            if (is_bool($formatted)) {
                return [$key => $formatted ? '1' : '0'];
            }

            return [$key => sprintf('%s', $formatted)];
        })
            ->values()
            ->all();

        $query = Str::replace($keys, $formattedParameters, $query);

        return $query;
    }

    protected function getFormattedParameter(int|string|float|bool|null $value): int|string|float|bool
    {
        if (is_null($value)) {
            return 'NULL';
        }

        if (is_string($value)) {
            return sprintf("'%s'", $value);
        }

        return $value;
    }
}
