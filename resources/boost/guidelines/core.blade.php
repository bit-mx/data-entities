## Data Entities

`bit-mx/data-entities` executes stored procedures (SQL Server, MySQL) through Laravel's DB facade with typed parameters, accessors, middleware, caching, and test fakes. The SQL dialect is resolved automatically from the connection driver via query executors (`data-entities.executers` config map).

### Conventions

- Put Data Entity classes in `app/DataEntities`.
- Extend `BitMx\DataEntities\DataEntity` and implement `resolveStoreProcedure(): string`.
- Override `defaultParameters(): array` (protected) for input parameters.
- Default response shape is a collection. Use `#[SingleItemResponse]` for a single row.
- Do NOT use removed v3 APIs: `$method`, `$responseType`, or `BitMx\DataEntities\Enums\Method`.
- Namespace is always `BitMx\DataEntities\...` (never `DataEntities\...`).

### Artisan commands

- `php artisan make:data-entity {name}`
- `php artisan make:data-entity {name} --from-procedure=spName --connection=sqlsrv`
- `php artisan make:data-entity-factory {name}`
- `php artisan make:data-entity-mutator {name}`
- `php artisan make:data-entity-accessor {name}`
- `php artisan data-entities:list` — inventory entities → procedure/connection
- `php artisan data-entities:check` — detect drift vs DB procedure signatures

### Core usage

@verbatim
<code-snippet name="Create and execute a Data Entity" lang="php">
use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;

#[SingleItemResponse]
class GetPostDataEntity extends DataEntity
{
    public function __construct(protected int $postId) {}

    public function resolveStoreProcedure(): string
    {
        return 'spListPost';
    }

    protected function defaultParameters(): array
    {
        return ['post_id' => $this->postId];
    }
}

$response = (new GetPostDataEntity(1))->execute();
$data = $response->data();
</code-snippet>
@endverbatim

### Testing

@verbatim
<code-snippet name="Fake a Data Entity in tests" lang="php">
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;

DataEntity::fake([
    GetPostDataEntity::class => MockResponse::make([
        'id' => 1,
        'title' => 'Post title',
    ]),
]);

$response = (new GetPostDataEntity(1))->execute();
DataEntity::assertExecutedOnce(GetPostDataEntity::class);
</code-snippet>
@endverbatim

### Best practices

- Prefer mutators (`mutators()`) for input casts and accessors (`accessors()`) for response casts.
- Use `alias()` to rename SQL columns before accessors run.
- Use `defaultOutputParameters()` for stored procedure output params; read them with `$response->output()`. On SQL Server they map to `DECLARE`/`OUTPUT`; on MySQL they map to session variables (the declared SQL type is ignored).
- Override `resolveQueryExecutor(): ?string` on a Data Entity to force a specific query executor; otherwise it is resolved from the connection driver.
- For multi-entity atomic work on one connection, use `DataEntity::transaction(fn () => ..., connection: 'sqlsrv')` or `DB::connection(...)->transaction(...)`.
- Use `AlwaysThrowOnError` when failures should throw automatically.
- For caching, implement `Cacheable` and use the `HasCache` trait; call `invalidateCache()` / `disableCaching()` on the Data Entity instance, not on the Response.
- For large result sets, use `#[UseLazyQuery]` with `$response->stream()` (single-pass) or `$response->lazy()` (re-iterable). Incompatible with `#[SingleItemResponse]` and output parameters.
- Activate the `data-entities-development` skill for detailed patterns.
