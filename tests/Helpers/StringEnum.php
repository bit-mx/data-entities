<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Tests\Helpers;

enum StringEnum: string
{
    case PAID = 'paid';
    case PENDING = 'pending';
    case EXPIRED = 'expired';
}
