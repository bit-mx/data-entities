<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Introspection\Contracts;

use BitMx\DataEntities\Introspection\ProcedureParameter;

interface ProcedureIntrospectorContract
{
    public function procedureExists(string $procedure): bool;

    /**
     * @return list<ProcedureParameter>
     */
    public function parameters(string $procedure): array;
}
