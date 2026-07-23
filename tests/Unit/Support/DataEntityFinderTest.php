<?php

declare(strict_types=1);

use BitMx\DataEntities\Support\DataEntityFinder;
use Illuminate\Support\Facades\File;

it('finds instantiable Data Entity classes under a path', function () {
    $directory = app_path('DataEntities');
    File::ensureDirectoryExists($directory);

    $path = $directory.'/FinderSampleDataEntity.php';
    File::put($path, <<<'PHP'
<?php

namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;

class FinderSampleDataEntity extends DataEntity
{
    public function resolveStoreProcedure(): string
    {
        return 'sp_finder_sample';
    }
}
PHP);

    $entities = (new DataEntityFinder)->find('app/DataEntities');

    expect($entities)->toContain('App\\DataEntities\\FinderSampleDataEntity');

    File::delete($path);
});
