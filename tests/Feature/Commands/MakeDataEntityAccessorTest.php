<?php

use BitMx\DataEntities\Commands\MakeDataEntityAccessor;

use function Pest\Laravel\artisan;

it('generates a new DataEntity accessor', function () {
    $name = 'CustomAccessor';

    artisan(MakeDataEntityAccessor::class, ['name' => $name])
        ->assertSuccessful()
        ->execute();

    $this->assertFileExists(app_path("DataEntityAccessors/{$name}.php"));
});
