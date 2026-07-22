## Data Entities

`bit-mx/data-entities` executes SQL Server stored procedures through Laravel's DB facade with typed parameters, accessors, middleware, caching, and test fakes.

### Conventions

- Put Data Entity classes in `app/DataEntities`.
- Extend `BitMx\DataEntities\DataEntity` and implement `resolveStoreProcedure(): string`.
- Override `defaultParameters(): array` (protected) for input parameters.
- Default response shape is a collection. Use `#[SingleItemResponse]` for a single row.
- Do NOT use removed v3 APIs: `$method`, `$responseType`, or `BitMx\DataEntities\Enums\Method`.
- Namespace is always `BitMx\DataEntities\...` (never `DataEntities\...`).

### Artisan commands

- `php artisan make:data-entity {name}`
- `php artisan make:data-entity-factory {name}`
- `php artisan make:data-entity-mutator {name}`
- `php artisan make:data-entity-accessor {name}`

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
- Use `defaultOutputParameters()` for SQL Server OUTPUT params; read them with `$response->output()`.
- Use `AlwaysThrowOnError` when failures should throw automatically.
- For caching, implement `Cacheable` and use the `HasCache` trait; call `invalidateCache()` / `disableCaching()` on the Data Entity instance, not on the Response.
- For large result sets, use `#[UseLazyQuery]` and `$response->lazy()` (incompatible with `#[SingleItemResponse]`).
- Activate the `data-entities-development` skill for detailed patterns.
