<?php

use BitMx\DataEntities\Commands\MakeDataEntityMutator;

use function Pest\Laravel\artisan;

it('generates a new DataEntity mutator', function () {
    $name = 'CustomMutator';

    artisan(MakeDataEntityMutator::class, ['name' => $name])
        ->assertSuccessful()
        ->execute();

    $this->assertFileExists(app_path("DataEntityMutators/{$name}.php"));
});
