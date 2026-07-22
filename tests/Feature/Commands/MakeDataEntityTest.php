<?php

use BitMx\DataEntities\Commands\MakeDataEntity;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\artisan;

it('generates a new DataEntity', function () {
    $name = 'UserDataEntity';
    $path = app_path("DataEntities/{$name}.php");

    File::delete($path);

    artisan(MakeDataEntity::class, ['name' => $name])
        ->assertSuccessful()
        ->execute();

    $this->assertFileExists($path);

    $contents = file_get_contents($path);

    expect($contents)
        ->toContain('namespace App\DataEntities;')
        ->toContain("class {$name} extends DataEntity")
        ->toContain('use BitMx\DataEntities\DataEntity;')
        ->toContain('public function resolveStoreProcedure(): string')
        ->toContain('protected function defaultParameters(): array');
});
