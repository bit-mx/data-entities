<?php

use BitMx\DataEntities\Introspection\Contracts\ProcedureIntrospectorContract;
use BitMx\DataEntities\Introspection\ProcedureIntrospectorResolver;
use BitMx\DataEntities\Introspection\ProcedureParameter;
use BitMx\DataEntities\Support\DataEntityFinder;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    $directory = app_path('DataEntities');
    File::ensureDirectoryExists($directory);

    File::put($directory.'/DriftCheckDataEntity.php', <<<'PHP'
<?php

namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;

class DriftCheckDataEntity extends DataEntity
{
    public function resolveStoreProcedure(): string
    {
        return 'sp_drift_check';
    }

    protected function defaultParameters(): array
    {
        return ['author_id' => 1];
    }
}
PHP);
});

afterEach(function () {
    File::delete(app_path('DataEntities/DriftCheckDataEntity.php'));
});

it('reports missing procedures', function () {
    $finder = mock(DataEntityFinder::class);
    $finder->shouldReceive('find')->andReturn(['App\\DataEntities\\DriftCheckDataEntity']);

    require_once app_path('DataEntities/DriftCheckDataEntity.php');

    $introspector = mock(ProcedureIntrospectorContract::class);
    $introspector->shouldReceive('procedureExists')->with('sp_drift_check')->andReturn(false);

    $resolver = mock(ProcedureIntrospectorResolver::class);
    $resolver->shouldReceive('resolve')->andReturn($introspector);

    $this->app->instance(DataEntityFinder::class, $finder);
    $this->app->instance(ProcedureIntrospectorResolver::class, $resolver);

    $this->artisan('data-entities:check')
        ->assertFailed();
});

it('passes when procedure signature matches', function () {
    $finder = mock(DataEntityFinder::class);
    $finder->shouldReceive('find')->andReturn(['App\\DataEntities\\DriftCheckDataEntity']);

    require_once app_path('DataEntities/DriftCheckDataEntity.php');

    $introspector = mock(ProcedureIntrospectorContract::class);
    $introspector->shouldReceive('procedureExists')->with('sp_drift_check')->andReturn(true);
    $introspector->shouldReceive('parameters')->with('sp_drift_check')->andReturn([
        new ProcedureParameter('author_id', 'INT', isOutput: false, isInput: true),
    ]);

    $resolver = mock(ProcedureIntrospectorResolver::class);
    $resolver->shouldReceive('resolve')->andReturn($introspector);

    $this->app->instance(DataEntityFinder::class, $finder);
    $this->app->instance(ProcedureIntrospectorResolver::class, $resolver);

    $this->artisan('data-entities:check')
        ->assertSuccessful();
});
