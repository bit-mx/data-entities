<?php

namespace BitMx\DataEntities\Responses\Mutators;

use BitMx\DataEntities\Accessors\AsArray;
use BitMx\DataEntities\Accessors\AsBool;
use BitMx\DataEntities\Accessors\AsCollection;
use BitMx\DataEntities\Accessors\AsDate;
use BitMx\DataEntities\Accessors\AsDateImmutable;
use BitMx\DataEntities\Accessors\AsDecimal;
use BitMx\DataEntities\Accessors\AsInteger;
use BitMx\DataEntities\Accessors\AsObject;
use BitMx\DataEntities\Accessors\AsString;

class AccessorsAlias
{
    /**
     * @return array<array-key, mixed>
     */
    public static function get(): array
    {
        return [
            'decimal' => AsDecimal::class,
            'float' => AsDecimal::class,
            'integer' => AsInteger::class,
            'int' => AsInteger::class,
            'string' => AsString::class,
            'bool' => AsBool::class,
            'boolean' => AsBool::class,
            'datetime' => AsDate::class,
            'date' => AsDate::class,
            'datetime_immutable' => AsDateImmutable::class,
            'date_immutable' => AsDateImmutable::class,
            'array' => AsArray::class,
            'object' => AsObject::class,
            'collection' => AsCollection::class,
        ];
    }
}
