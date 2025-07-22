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

        $keys = $this->pendingQuery->parameters()->keys()->map(fn (string $key) => sprintf(':%s', $key));

        $parameters = (new ParametersProcessor($this->pendingQuery))->process();

        /** @var list<string> $parameters */
        $parameters = collect($parameters)->mapWithKeys(function (mixed $value, string $key) {
            return [
                $key => $this->getFormattedParameter($value),
            ];
        })
            ->values()
            ->all();

        $query = Str::replace($keys, $parameters, $query);

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
