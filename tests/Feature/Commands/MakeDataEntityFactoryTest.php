<?php

use BitMx\DataEntities\Commands\MakeDataEntityFactory;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\artisan;

it('generates a new DataEntity factory', function () {
    $name = 'UserDataEntityFactory';
    $path = base_path("tests/DataEntityFactories/{$name}.php");

    File::delete($path);

    artisan(MakeDataEntityFactory::class, ['name' => $name])
        ->assertSuccessful()
        ->execute();

    $this->assertFileExists($path);

    $contents = file_get_contents($path);

    expect($contents)
        ->toContain('namespace Tests\DataEntityFactories;')
        ->toContain("class {$name} extends DataEntityFactory")
        ->toContain('use BitMx\DataEntities\Factories\DataEntityFactory;')
        ->toContain('public function definition(): array');
});
