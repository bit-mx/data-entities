<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Introspection;

use BitMx\DataEntities\Introspection\Contracts\ProcedureIntrospectorContract;
use Illuminate\Support\Facades\DB;

class MySqlProcedureIntrospector implements ProcedureIntrospectorContract
{
    public function __construct(
        protected readonly string $connection,
    ) {}

    public function procedureExists(string $procedure): bool
    {
        $name = $this->unqualifiedName($procedure);

        $result = DB::connection($this->connection)->selectOne(
            'SELECT COUNT(*) AS aggregate
             FROM information_schema.routines
             WHERE routine_schema = DATABASE()
               AND routine_type = \'PROCEDURE\'
               AND routine_name = ?',
            [$name]
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
        $name = $this->unqualifiedName($procedure);

        $rows = DB::connection($this->connection)->select(
            'SELECT PARAMETER_NAME, DATA_TYPE, DTD_IDENTIFIER, PARAMETER_MODE
             FROM information_schema.parameters
             WHERE SPECIFIC_SCHEMA = DATABASE()
               AND SPECIFIC_NAME = ?
               AND PARAMETER_NAME IS NOT NULL
             ORDER BY ORDINAL_POSITION',
            [$name]
        );

        $parameters = [];

        foreach ($rows as $row) {
            /** @var array{PARAMETER_NAME: string, DATA_TYPE: string, DTD_IDENTIFIER: string|null, PARAMETER_MODE: string} $data */
            $data = (array) $row;
            $mode = strtoupper($data['PARAMETER_MODE']);

            $dtdIdentifier = $data['DTD_IDENTIFIER'];

            $parameters[] = new ProcedureParameter(
                name: $data['PARAMETER_NAME'],
                sqlType: strtoupper($dtdIdentifier !== null && $dtdIdentifier !== '' ? $dtdIdentifier : $data['DATA_TYPE']),
                isOutput: in_array($mode, ['OUT', 'INOUT'], true),
                isInput: in_array($mode, ['IN', 'INOUT'], true),
            );
        }

        return $parameters;
    }

    protected function unqualifiedName(string $procedure): string
    {
        $parts = explode('.', $procedure);
        $last = end($parts);

        return $last !== '' ? $last : $procedure;
    }
}
