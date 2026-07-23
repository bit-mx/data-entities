<?php

declare(strict_types=1);

use BitMx\DataEntities\Commands\MakeDataEntityAccessor;
use Illuminate\Support\Facades\File;

use function Pest\Laravel\artisan;

it('generates a new DataEntity accessor', function () {
    $name = 'CustomAccessor';
    $path = app_path("DataEntityAccessors/{$name}.php");

    File::delete($path);

    artisan(MakeDataEntityAccessor::class, ['name' => $name])
        ->assertSuccessful()
        ->execute();

    $this->assertFileExists($path);

    $contents = file_get_contents($path);

    expect($contents)
        ->toContain('namespace App\DataEntityAccessors;')
        ->toContain("class {$name} implements Accessable")
        ->toContain('use BitMx\DataEntities\Contracts\Accessable;')
        ->toContain('public function get(string $key, mixed $value, array $data): mixed');
});
