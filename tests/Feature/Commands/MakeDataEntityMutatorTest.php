<?php

use BitMx\DataEntities\Commands\MakeDataEntityMutator;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\artisan;

it('generates a new DataEntity mutator', function () {
    $name = 'CustomMutator';
    $path = app_path("DataEntityMutators/{$name}.php");

    File::delete($path);

    artisan(MakeDataEntityMutator::class, ['name' => $name])
        ->assertSuccessful()
        ->execute();

    $this->assertFileExists($path);

    $contents = file_get_contents($path);

    expect($contents)
        ->toContain('namespace App\DataEntityMutators;')
        ->toContain("class {$name} implements Mutable")
        ->toContain('use BitMx\DataEntities\Contracts\Mutable;')
        ->toContain('public function transform(string $key, mixed $value, array $parameters): mixed');
});
