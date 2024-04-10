<?php

use BitMx\DataEntities\Commands\MakeDataEntityFactory;

use function Pest\Laravel\artisan;

it('generates a new DataEntity', function () {
    $name = 'UserDataEntityFactory';

    artisan(MakeDataEntityFactory::class, ['name' => $name])
        ->assertSuccessful()
        ->execute();

    $this->assertFileExists(base_path("tests/DataEntityFactories/{$name}.php"));
});
