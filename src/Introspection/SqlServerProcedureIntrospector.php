<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Introspection;

use BitMx\DataEntities\Introspection\Contracts\ProcedureIntrospectorContract;
use Illuminate\Support\Facades\DB;

class SqlServerProcedureIntrospector implements ProcedureIntrospectorContract
{
    public function __construct(
        protected readonly string $connection,
    ) {}

    public function procedureExists(string $procedure): bool
    {
        [$schema, $name] = $this->splitName($procedure);

        $result = DB::connection($this->connection)->selectOne(
            'SELECT COUNT(*) AS aggregate
             FROM sys.procedures p
             INNER JOIN sys.schemas s ON s.schema_id = p.schema_id
             WHERE s.name = ? AND p.name = ?',
            [$schema, $name]
        );

        /** @var array{aggregate?: int|string|null} $row */
        $row = (array) $result;

        return (int) ($row['aggregate'] ?? 0) > 0;
    }

    /**
     * @return list<ProcedureParameter>
     */
    public function parameters(string $procedure): array
    {
        [$schema, $name] = $this->splitName($procedure);

        $rows = DB::connection($this->connection)->select(
            'SELECT
                REPLACE(par.name, \'@\', \'\') AS parameter_name,
                TYPE_NAME(par.user_type_id) AS data_type,
                par.max_length,
                par.precision,
                par.scale,
                par.is_output
             FROM sys.parameters par
             INNER JOIN sys.procedures p ON p.object_id = par.object_id
             INNER JOIN sys.schemas s ON s.schema_id = p.schema_id
             WHERE s.name = ? AND p.name = ?
             ORDER BY par.parameter_id',
            [$schema, $name]
        );

        $parameters = [];

        foreach ($rows as $row) {
            /** @var array{parameter_name: string, data_type: string, max_length: int, precision: int, scale: int, is_output: int|bool} $data */
            $data = (array) $row;

            $parameters[] = new ProcedureParameter(
                name: $data['parameter_name'],
                sqlType: $this->formatSqlType($data),
                isOutput: (bool) $data['is_output'],
                isInput: ! (bool) $data['is_output'],
            );
        }

        return $parameters;
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function splitName(string $procedure): array
    {
        if (str_contains($procedure, '.')) {
            [$schema, $name] = explode('.', $procedure, 2);

            return [$schema, $name];
        }

        return ['dbo', $procedure];
    }

    /**
     * @param  array{parameter_name?: string, data_type: string, max_length: int, precision: int, scale: int, is_output?: int|bool}  $row
     */
    protected function formatSqlType(array $row): string
    {
        $type = strtoupper($row['data_type']);

        if (in_array($type, ['NVARCHAR', 'VARCHAR', 'CHAR', 'NCHAR', 'VARBINARY'], true)) {
            $length = $row['max_length'];

            if (in_array($type, ['NVARCHAR', 'NCHAR'], true) && $length > 0) {
                $length = intdiv($length, 2);
            }

            return $length > 0 ? sprintf('%s(%d)', $type, $length) : $type;
        }

        if (in_array($type, ['DECIMAL', 'NUMERIC'], true)) {
            return sprintf('%s(%d,%d)', $type, $row['precision'], $row['scale']);
        }

        return $type;
    }
}
