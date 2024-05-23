<?php

namespace BitMx\DataEntities\Parameters;

use BitMx\DataEntities\Casts\AsBool;
use BitMx\DataEntities\Casts\AsDate;
use BitMx\DataEntities\Casts\AsDateTimeFormated;
use BitMx\DataEntities\Casts\AsInteger;
use BitMx\DataEntities\Casts\AsJson;
use BitMx\DataEntities\Casts\AsString;

class CastAlias
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
