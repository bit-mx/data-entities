<?php

namespace BitMx\DataEntities\Parameters;

use BitMx\DataEntities\Mutators\AsBool;
use BitMx\DataEntities\Mutators\AsDate;
use BitMx\DataEntities\Mutators\AsDateTimeFormated;
use BitMx\DataEntities\Mutators\AsInteger;
use BitMx\DataEntities\Mutators\AsJson;
use BitMx\DataEntities\Mutators\AsString;

class MutatorsAlias
{
    /**
     * @return array<string, mixed>
     */
    public static function get(): array
    {
        return [
            'int' => AsInteger::class,
            'datetime' => AsDateTimeFormated::class,
            'date' => AsDate::class,
            'bool' => AsBool::class,
            'string' => AsString::class,
            'json' => AsJson::class,
        ];
    }
}
