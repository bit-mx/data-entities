<?php

use BitMx\DataEntities\Commands\MakeDataEntity;

use function Pest\Laravel\artisan;

it('generates a new DataEntity', function () {
    $name = 'UserDataEntity';

    artisan(MakeDataEntity::class, ['name' => $name])
        ->assertSuccessful()
        ->execute();

    $this->assertFileExists(app_path("DataEntities/{$name}.php"));
});
