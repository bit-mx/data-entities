<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Tests\Helpers;

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;

#[SingleItemResponse]
class AssertableSecondaryEntity extends DataEntity
{
    public function resolveStoreProcedure(): string
    {
        return 'sp_assert_secondary';
    }
}
