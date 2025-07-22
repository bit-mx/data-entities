<?php

declare(strict_types=1);

namespace BitMx\DataEntities\Factories;

class CreateFactoryData
{
    /**
     * @return array<array-key, mixed>
     */
    public function __invoke(DataEntityFactory $factory): array
    {
        return $factory->getData()->getData();
    }
}
