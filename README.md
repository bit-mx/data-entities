# Data Entities

Execute stored procedures (SQL Server, MySQL) in Laravel without all the boilerplate code.

Table of Contents
=================

* [Introduction](#introduction)
* [Installation](#installation)
* [Setup](#setup)
* [Compatibility](#compatibility)
* [Laravel Boost](#laravel-boost)
* [Getting Started](#getting-started)
    * [Create a Data Entity](#create-a-data-entity)
    * [Creating a DataEntity class](#creating-a-dataentity-class)
    * [Connection](#connection)
    * [Database support](#database-support)
    * [Execute the Data Entity](#execute-the-data-entity)
    * [Output parameters](#output-parameters)
    * [Mutators](#mutators)
        * [Available mutators](#available-mutators)
        * [Automatic mutators](#automatic-mutators)
        * [Custom mutators](#custom-mutators)
    * [Accessors](#accessors)
        * [Available accessors](#available-accessors)
        * [Custom accessor](#custom-accessor)
    * [Column aliases](#column-aliases)
    * [Response useful methods](#response-useful-methods)
        * [data](#data)
        * [Data with a key](#data-with-a-key)
        * [Data with a key and a default value](#data-with-a-key-and-a-default-value)
        * [rawData](#rawdata)
        * [output](#output)
        * [Add data value](#add-data-value)
        * [Merge data](#merge-data)
        * [As object](#as-object)
        * [As collection](#as-collection)
        * [isEmpty / isNotEmpty](#isempty--isnotempty)
        * [success](#success)
        * [failed](#failed)
        * [throw](#throw)
        * [getError](#geterror)
        * [isCached](#iscached)
    * [Boot](#boot)
    * [Events](#events)
    * [Middlewares](#middlewares)
    * [Plugins](#plugins)
        * [AlwaysThrowOnError](#alwaysthrowonerror)
        * [HasCache](#hascache)
        * [HasRetries](#hasretries)
    * [Lazy Collection](#lazy-collection)
    * [Data Transfer objects](#data-transfer-objects)
    * [Debugging](#debugging)
    * [Testing](#testing)
        * [Mocking the Data Entity](#mocking-the-data-entity)
        * [Assertions](#assertions)
        * [Using factories](#using-factories)
        * [Response type](#response-type)
    * [Upgrading to version 4](#upgrading-to-version-4)

## Introduction

Data Entities is a library that allows you to execute stored procedures easily. It is a wrapper around
the Laravel's DB Facade. SQL Server and MySQL are supported out of the box, and you can register your own
query executor for other database engines.

## Installation

You can install the package via composer:

```bash
composer require bit-mx/data-entities
```

## Setup

You need to publish the configuration file to set the connection name.

```bash
php artisan vendor:publish --provider="BitMx\DataEntities\DataEntitiesServiceProvider" --tag="config"
```

This command will create a new configuration file in the `config` directory.

```php
use BitMx\DataEntities\Executers\MySqlQueryExecutor;
use BitMx\DataEntities\Executers\SqlServerQueryExecutor;

return [
    'database' => env('DATA_ENTITIES_CONNECTION', 'sqlsrv'),

    'executers' => [
        'sqlsrv' => SqlServerQueryExecutor::class,
        'mysql' => MySqlQueryExecutor::class,
    ],
];
```

The `executers` map defines which query executor is used for each database driver. See [Database support](#database-support).

## Compatibility

This package is compatible with Laravel 11.x, 12.x, and 13.x.

It requires PHP 8.4 or above.

> Laravel 11 is past security support. Prefer Laravel 12 or 13 for new apps; CI still runs Laravel 11 for compatibility.

CI runs Pest against PHP 8.4/8.5 and Laravel 11/12/13 (PHP 8.5 × Laravel 11 excluded).

MySQL integration tests live in `tests/Integration` and run in CI against a real MySQL service.
Locally:

```bash
DATA_ENTITIES_INTEGRATION_MYSQL=1 DB_PASSWORD=password vendor/bin/pest tests/Integration
```

## Laravel Boost

If your application uses [Laravel Boost](https://laravel.com/docs/boost), this package ships AI guidelines and an agent skill that Boost discovers automatically when you run:

```bash
php artisan boost:install
# or later
php artisan boost:update
```

No extra package dependency is required. Install Boost in your application as a dev dependency (`composer require laravel/boost --dev`), then run the commands above so agents pick up the `data-entities` guidelines and the `data-entities-development` skill.

## Getting Started

### Create a Data Entity

To create a Data Entity, you need to extend the DataEntity class and implement the `resolveStoreProcedure` method with the
name of the stored procedure you want to execute.

You can also override the `defaultParameters` method to set the default parameters for the stored procedure.

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;

class GetAllPostsDataEntity extends DataEntity
{
    public function __construct(
        protected int $authorId,
    ) {
    }

    #[\Override]
    public function resolveStoreProcedure(): string
    {
        return 'spListAllPost';
    }

    #[\Override]
    protected function defaultParameters(): array
    {
        return [
            'author_id' => $this->authorId,
        ];
    }
}
```

You can also use the `parameters` method to set the parameters for the stored procedure.

Override `requiredParameters()` to validate required keys before the query hits the database.
Missing keys throw `MissingRequiredParameterException` with a clear parameter name:

```php
public function requiredParameters(): array
{
    return ['author_id', 'status'];
}
```

```php
use App\DataEntities\GetAllPostsDataEntity;

$dataEntity = new GetAllPostsDataEntity(1);

$dataEntity->parameters()->add('tag', 'laravel');
```

By default, the Data Entity will return a Response with a collection of records. You can change this by setting the
PHP attribute `SingleItemResponse`. This way, you can return a single record instead of a collection.

```php
namespace App\DataEntities;

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;

#[SingleItemResponse]
class GetPostDataEntity extends DataEntity
{
    public function __construct(
        protected int $postId,
    ) {
    }

    #[\Override]
    public function resolveStoreProcedure(): string
    {
        return 'spListPost';
    }

    #[\Override]
    protected function defaultParameters(): array
    {
        return [
            'post_id' => $this->postId,
        ];
    }
}
```

### Creating a DataEntity class

You can use the artisan command to create a new Data Entity:

```bash
php artisan make:data-entity GetAllPostsDataEntity
```

This command will create a new Data Entity in the `app/DataEntities` directory.

You can also generate a Data Entity from an existing stored procedure signature:

```bash
php artisan make:data-entity CreatePostDataEntity --from-procedure=dbo.spCreatePost --connection=sqlsrv
```

The generator introspects SQL Server (`sys.parameters`) or MySQL (`information_schema.parameters`) and fills the constructor, `defaultParameters()`, suggested mutators, and `defaultOutputParameters()`.

Inventory and drift commands:

```bash
php artisan data-entities:list
php artisan data-entities:check
```

`data-entities:list` prints each entity with its stored procedure and connection.
`data-entities:check` verifies that each procedure exists and, when the entity can be constructed without arguments, compares input/output parameter names against the database signature.

### Connection

You can set the connection by overriding `resolveDatabaseConnection()`. It may return a Laravel
connection **name** (`string`) or a live `Illuminate\Database\Connection` instance.

Precedence when executing:

`onConnection(...)` → `resolveDatabaseConnection()` → `config('data-entities.database')`

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;

class GetAllPostsDataEntity extends DataEntity
{
    // ...

    #[\Override]
    public function resolveDatabaseConnection(): string|\Illuminate\Database\Connection
    {
        return 'sqlsrv';
    }
}
```

When you integrate several legacy systems, prefer one abstract base entity per system so every procedure shares the same connection (and optional defaults):

```php
namespace App\DataEntities\Erp;

use BitMx\DataEntities\DataEntity;

abstract class ErpDataEntity extends DataEntity
{
    #[\Override]
    public function resolveDatabaseConnection(): string|\Illuminate\Database\Connection
    {
        return 'erp_sqlsrv';
    }
}

class GetCustomerDataEntity extends ErpDataEntity
{
    public function resolveStoreProcedure(): string
    {
        return 'dbo.spGetCustomer';
    }
}
```

```php
namespace App\DataEntities\Crm;

use BitMx\DataEntities\DataEntity;

abstract class CrmDataEntity extends DataEntity
{
    #[\Override]
    public function resolveDatabaseConnection(): string|\Illuminate\Database\Connection
    {
        return 'crm_mysql';
    }
}
```

For a connection that is **not** defined in `config/database.php`, build one at runtime with
Laravel's `DB::build()` (Laravel 11.31+) and return or pass the `Connection` instance.
Credentials come from your own values (host, user, password, etc.), not from a named config entry:

```php
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

#[\Override]
public function resolveDatabaseConnection(): string|Connection
{
    return DB::build([
        'driver' => 'sqlsrv',
        'host' => $this->host,
        'port' => $this->port,
        'database' => $this->database,
        'username' => $this->username,
        'password' => $this->password,
    ]);
}
```

Or override the connection at runtime without changing the entity class:

```php
use Illuminate\Support\Facades\DB;

(new GetCustomerDataEntity($id))
    ->onConnection(DB::build([
        'driver' => 'mysql',
        'host' => $host,
        'database' => $database,
        'username' => $user,
        'password' => $password,
    ]))
    ->execute();

(new GetCustomerDataEntity($id))
    ->onConnection('tenant_mysql') // connection name from config/database.php
    ->execute();
```

If the connection is already registered under a name (for example `tenant_123`), you can still use
`DB::connection('tenant_123')` or `onConnection('tenant_123')` as before.

Organize classes under `app/DataEntities/{System}/` and point `data-entities:list` / `data-entities:check` at those paths with `--path=app/DataEntities/Erp`.

You can optionally set a per-entity query timeout in seconds via `queryTimeout()`.
When set, the package applies `PDO::ATTR_TIMEOUT` on the connection before execution:

```php
public function queryTimeout(): ?int
{
    return 30;
}
```

### Transactions

When several Data Entities must succeed or fail together, wrap them in a transaction on the **same** connection:

```php
use BitMx\DataEntities\DataEntity;
use Illuminate\Support\Facades\DB;

DB::connection('sqlsrv')->transaction(function () {
    (new CreateOrderDataEntity($payload))->execute();
    (new ReserveInventoryDataEntity($orderId))->execute();
});
```

Or use the helper (defaults to `config('data-entities.database')`). The second argument accepts a connection name or a `Connection` instance:

```php
DataEntity::transaction(function () {
    (new CreateOrderDataEntity($payload))->execute();
    (new ReserveInventoryDataEntity($orderId))->execute();
}, connection: 'sqlsrv');

DataEntity::transaction(function () {
    (new CreateOrderDataEntity($payload))->execute();
}, connection: $dynamicConnection);
```

Entities that target different connections cannot share one transaction.

### Database support

The package generates the correct SQL for each database engine through query executors. The executor is resolved
automatically from the driver of the connection used by the Data Entity:

- `sqlsrv` → `SqlServerQueryExecutor` (`EXEC sp @param = :param`)
- `mysql` → `MySqlQueryExecutor` (`CALL sp(:param)`)

If the connection driver has no executor registered in the `executers` config map, an
`UnsupportedQueryExecutorException` is thrown.

Parameter names, stored procedure names, and output SQL types are validated before the query is
compiled. Names must be identifiers (`post_id`, `dbo.spListPost`); SQL types must look like
`INT`, `NVARCHAR(100)`, or `DECIMAL(10,2)`. Invalid values throw `InvalidIdentifierException`
to prevent SQL injection through interpolated identifiers.

You can force a specific executor for a single Data Entity by overriding the `resolveQueryExecutor` method:

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Executers\MySqlQueryExecutor;

class GetAllPostsDataEntity extends DataEntity
{
    // ...

    #[\Override]
    public function resolveQueryExecutor(): ?string
    {
        return MySqlQueryExecutor::class;
    }
}
```

To support another database engine, create a class that implements
`BitMx\DataEntities\Executers\Contracts\QueryExecutorContract` (or extends
`BitMx\DataEntities\Executers\AbstractQueryExecutor`) and register it in the config:

```php
use App\DataEntityExecuters\PostgresQueryExecutor;

return [
    // ...

    'executers' => [
        'sqlsrv' => SqlServerQueryExecutor::class,
        'mysql' => MySqlQueryExecutor::class,
        'pgsql' => PostgresQueryExecutor::class,
    ],
];
```

### Execute the Data Entity

To execute the Data Entity, you need to call the `execute` method on the Data Entity instance.

```php
use App\DataEntities\GetAllPostsDataEntity;

$dataEntity = new GetAllPostsDataEntity(1);

$response = $dataEntity->execute();

$data = $response->data();
```

The `execute` method returns a Response object that contains the data returned by the stored procedure.

### Output parameters

Stored procedure output parameters are supported via `defaultOutputParameters()`. Map each output parameter name to its SQL type.

- On **SQL Server**, the package will `DECLARE` the variables, pass them as `OUTPUT`, and select them back into `$response->output()`.
- On **MySQL**, the package passes them as session variables (`CALL sp(:param, @out)`) and then reads them with a separate `SELECT @out AS out` after the call. The declared SQL type is ignored because MySQL does not require a `DECLARE` statement.

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;

class CreatePostDataEntity extends DataEntity
{
    public function __construct(
        protected string $title,
    ) {
    }

    #[\Override]
    public function resolveStoreProcedure(): string
    {
        return 'spCreatePost';
    }

    #[\Override]
    protected function defaultParameters(): array
    {
        return [
            'title' => $this->title,
        ];
    }

    #[\Override]
    protected function defaultOutputParameters(): array
    {
        return [
            'new_id' => 'INT',
        ];
    }
}
```

```php
use App\DataEntities\CreatePostDataEntity;

$dataEntity = new CreatePostDataEntity('Hello world');

$response = $dataEntity->execute();

$newId = $response->output('new_id');
```

You can also add output parameters at runtime with `$dataEntity->outputParameters()->add('name', 'INT')`.

Use `$response->rawOutput()` when you need the values before accessors/aliases are applied.

### Mutators

You can use the `mutators` method to transform the parameters before sending them to the stored procedure.

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;
use Carbon\Carbon;

class GetAllPostsDataEntity extends DataEntity
{
    // ...

    #[\Override]
    protected function defaultParameters(): array
    {
        return [
            'date' => Carbon::now(),
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function mutators(): array
    {
        return [
            'date' => 'datetime:Y-m-d H:i',
        ];
    }
}
```

This will transform the date parameter to a formatted date string before sending it to the stored procedure.

#### Available mutators

- **datetime:**
  Converts the value to a datetime string using the specified format.
  You can pass a format as an argument to the cast.
  Examples:

    - `datetime` Returns `Y-m-d H:i:s`
    - `datetime:Y-m-d`
    - `datetime:H:i:s`
    - `datetime:Y-m-d H:i:s`
- **date:**
  Converts the value to a date `Y-m-d`
- **bool:**
  Converts the value to a boolean as int.
  Example: If the value is `true`, it will be converted to `1`, and if it is `false`, it will be converted to `0`.
- **int:**
  Converts the value to an integer.
- **float / decimal:**
  Converts the value to a float. You can pass the number of decimals as an argument to the cast.
  Example:

      - `float` Returns a float rounded to 2 decimals.
      - `float:4` Returns a float rounded to 4 decimals.
      - `float:0` Returns a float rounded to 0 decimals.
      - `decimal` is an alias of `float`.
- **string:**
  Converts the value to a string.
- **json:**
  Converts the value to a JSON string.
  Example:

  - If you pass an array, it will be converted to a JSON string.
  - `[1, 2, 4]` will be converted to `"[1,2,4]"`.
  - `['name' => 'John']` will be converted to `'{"name":"John"}'`.
  - You can pass the JSON options as an argument to the cast.
  - `'json:'. JSON_PRETTY_PRINT` will return the JSON string with the `JSON_PRETTY_PRINT` option.
- **BackedEnum class-string:**
  You can also map a parameter to a backed enum class. The mutator will use the enum's value.

#### Automatic mutators

When no mutator is defined for a parameter, the package still transforms some types automatically:

- `bool` → `0` / `1`
- `BackedEnum` → enum value
- `DateTimeInterface` → `Y-m-d H:i:s`
- `null` and other scalars are passed through

### Custom mutators

You can create custom mutators by implementing the `Mutable` interface.

```php
namespace App\DataEntityMutators;

use BitMx\DataEntities\Contracts\Mutable;

class CustomMutator implements Mutable
{
    /**
     * {@inheritDoc}
     */
    public function transform(string $key, mixed $value, array $parameters): mixed
    {
        //
    }
}
```

You can create a new mutator using the artisan command.

```bash
php artisan make:data-entity-mutator CustomMutator
```

### Accessors

You can use the `accessors` method to transform the data returned by the stored procedure.

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;

class GetAllPostsDataEntity extends DataEntity
{
    // ...

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function accessors(): array
    {
        return [
            'contact_id' => 'integer',
        ];
    }
}
```

This will transform the `contact_id` key to an integer before returning the data.

#### Available accessors

- **datetime / date:**
  Converts the value to a `DateTime` instance.
- **datetime_immutable / date_immutable:**
  Converts the value to a `DateTimeImmutable` instance.
- **bool / boolean:**
  Converts the value to a boolean.
  Example: If the value is `1`, it will be converted to `true`.
- **int / integer:**
  Converts the value to an integer.
- **float / decimal:**
  Converts the value to a float.
- **string:**
  Converts the value to a string.
- **array:**
  Converts the value from a JSON string to an array.
- **object:**
  Converts the value from a JSON string to an object.
- **collection:**
  Converts the value from a JSON string to a Laravel Collection.
- **BackedEnum class-string:**
  You can map a column to a backed enum class (`Enum::tryFrom($value)`).

### Custom accessor

You can create custom accessors by implementing the `Accessable` interface.

```php
namespace App\DataEntityAccessors;

use BitMx\DataEntities\Contracts\Accessable;

class CustomAccessor implements Accessable
{
    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $value, array $data): mixed
    {
        //
    }
}
```

You can create a new accessor using the artisan command.

```bash
php artisan make:data-entity-accessor CustomAccessor
```

### Column aliases

You can rename response columns (and output parameter keys) using the `alias` method.
Aliases are applied before accessors.

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;

class GetAllPostsDataEntity extends DataEntity
{
    // ...

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function alias(): array
    {
        return [
            'post_title' => 'title',
            'post_body' => 'content',
        ];
    }
}
```

You can also set aliases at runtime with `$dataEntity->setAlias([...])`.

## Response useful methods

The Response object has some useful methods to work with the data returned by the stored procedure.

### data

The `data` method returns the data returned by the stored procedure as an array (after aliases and accessors).

```php
$data = $response->data();
```

### Data with a key

You can get the data with a key:

```php
$data = $response->data('key');
```

### Data with a key and a default value

You can get the data with a key and a default value:

```php
$data = $response->data('key', 'default value');
```

### rawData

Returns the data before aliases and accessors are applied:

```php
$data = $response->rawData();
$data = $response->rawData('key', 'default value');
```

### output

Returns stored procedure output parameter values:

```php
$output = $response->output();
$newId = $response->output('new_id');
```

### Add data value

You can add a value to the data array:

```php
$response->addData('key', 'value');
```

You can also pass an array:

```php
$response->addData(['key' => 'value']);
```

### Merge data

You can merge an array with the data array:

```php
$response->mergeData(['key' => 'value']);
```

### As object

You can get the data as an object:

```php
$data = $response->object();
```

### As collection

You can get the data as a collection:

```php
$data = $response->collect();
```

### isEmpty / isNotEmpty

```php
if ($response->isEmpty()) {
    // no rows
}

if ($response->isNotEmpty()) {
    // has rows
}
```

### success

The `success` method returns `true` if the stored procedure was executed successfully, and `false` otherwise.

```php
if ($response->success()) {
    // The stored procedure was executed successfully
} else {
    // There was an error executing the stored procedure
}
```

### failed

The `failed` method returns `true` if the stored procedure failed, and `false` otherwise.

```php
if ($response->failed()) {
    // There was an error executing the stored procedure
} else {
    // The stored procedure was executed successfully
}
```

Database failures (`QueryException` and `PDOException`) are captured into the Response as a soft failure
(`success()` is `false`). Programming errors such as invalid mutators or unsupported executors still throw.

### throw

By default, the Response object won't throw an exception if the stored procedure fails. You can throw an exception
manually using the `throw` method.

```php
$response->throw();
```

### getError

Returns the error message when the response failed:

```php
$message = $response->getError();
```

### isCached

Returns whether the response was served from cache (when using the `HasCache` plugin):

```php
$response->isCached();
```

## Boot

You can use the `boot` method to execute code before the stored procedure is executed.

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;

class GetAllPostsDataEntity extends DataEntity
{
    // ...

    #[\Override]
    public function boot(PendingQuery $pendingQuery): void
    {
        $pendingQuery->parameters()->add('tag', 'laravel');
    }
}
```

### Traits

You can use traits to add functionality to your Data Entities. Add a `boot{TraitName}` method so it runs during boot.

```php
trait Taggable
{
    public function bootTaggable(PendingQuery $pendingQuery): void
    {
        $pendingQuery->parameters()->add('tag', 'laravel');
    }
}
```

The `bootTaggable` method will be called before the stored procedure is executed.

## Events

Real database executions dispatch Laravel events you can listen to for logging or APM:

- `BitMx\DataEntities\Events\DataEntityExecuted` — successful execution
- `BitMx\DataEntities\Events\DataEntityFailed` — soft failure (`QueryException` / `PDOException`)

Both events expose the Data Entity, pending query, response, compiled SQL, and duration in milliseconds.
Failed events also expose the captured exception.

```php
use BitMx\DataEntities\Events\DataEntityExecuted;
use BitMx\DataEntities\Events\DataEntityFailed;
use Illuminate\Support\Facades\Event;

Event::listen(DataEntityExecuted::class, function (DataEntityExecuted $event) {
    logger()->info('data-entity.executed', [
        'entity' => $event->dataEntity::class,
        'duration_ms' => $event->durationMs,
    ]);
});

Event::listen(DataEntityFailed::class, function (DataEntityFailed $event) {
    logger()->warning('data-entity.failed', [
        'entity' => $event->dataEntity::class,
        'error' => $event->exception->getMessage(),
    ]);
});
```

Fake / mocked executions do not dispatch these events.

## Middlewares

You can use middlewares to execute code before and after the stored procedure is executed.

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\Response;

class GetAllPostsDataEntity extends DataEntity
{
    // ...

    #[\Override]
    public function boot(PendingQuery $pendingQuery): void
    {
        $pendingQuery->middleware()->onQuery(function (PendingQuery $pendingQuery) {
            $pendingQuery->parameters()->add('tag', 'laravel');
        });

        $pendingQuery->middleware()->onResponse(function (Response $response) {
            $response->addData('tag', 'laravel');

            return $response;
        });
    }
}
```

You can also use an invokable class as a middleware. This class should implement the `QueryMiddleware` or
`ResponseMiddleware` interface.

```php
use BitMx\DataEntities\Contracts\QueryMiddleware;
use BitMx\DataEntities\PendingQuery;

class PageMiddleware implements QueryMiddleware
{
    public function __invoke(PendingQuery $pendingQuery): PendingQuery
    {
        $pendingQuery->parameters()->add('page', 1);

        return $pendingQuery;
    }
}
```

```php
use BitMx\DataEntities\Contracts\ResponseMiddleware;
use BitMx\DataEntities\Responses\Response;

class TagMiddleware implements ResponseMiddleware
{
    public function __invoke(Response $response): Response
    {
        $response->addData('tag', 'laravel');

        return $response;
    }
}
```

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\PendingQuery;

class GetAllPostsDataEntity extends DataEntity
{
    // ...

    #[\Override]
    public function boot(PendingQuery $pendingQuery): void
    {
        $pendingQuery->middleware()->onQuery(new PageMiddleware());

        $pendingQuery->middleware()->onResponse(new TagMiddleware());
    }
}
```

## Plugins

You can use plugins to add functionality to your Data Entities.

### AlwaysThrowOnError

The `AlwaysThrowOnError` plugin will throw an exception if the stored procedure fails.

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Plugins\AlwaysThrowOnError;

class GetAllPostsDataEntity extends DataEntity
{
    use AlwaysThrowOnError;

    // ...
}
```

### HasCache

The `HasCache` plugin will cache the data returned by the stored procedure.

The Data Entity should implement the `Cacheable` interface.

```php
namespace App\DataEntities;

use BitMx\DataEntities\Contracts\Cacheable;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Plugins\HasCache;

class GetAllPostsDataEntity extends DataEntity implements Cacheable
{
    use HasCache;

    // ...

    public function cacheExpiresAt(): \DateTimeInterface
    {
        return now()->addMinutes(10);
    }
}
```

Optional hooks:

- `cacheKey(PendingQuery $pendingQuery): ?string` — custom cache key (default is a SHA-256 hash of the class, stored procedure, connection, parameters, and output parameters)
- `cacheDriver(): string` — cache store name (default: `config('cache.default')`)

The default cache key includes the database connection name, so the same entity executed against different connections does not share cache entries.

Cached responses store raw data and re-apply accessors when the response is restored, so non-idempotent accessors are not applied twice.

Cache payloads are unserialized with an allow-list of package classes only. If `cacheExpiresAt()` is in the past, the TTL is floored to 1 second.

You can invalidate the cache for the next execution using `invalidateCache()` on the Data Entity instance:

```php
use App\DataEntities\GetPostDataEntity;

$dataEntity = new GetPostDataEntity(1);

$dataEntity->invalidateCache();
$response = $dataEntity->execute();
```

Or you can disable caching temporarily using `disableCaching()`:

```php
use App\DataEntities\GetPostDataEntity;

$dataEntity = new GetPostDataEntity(1);

$dataEntity->disableCaching();
$response = $dataEntity->execute();
```

You can also clear an existing cache entry with `clearCache()`:

```php
$dataEntity->clearCache();
```

The Response object has an `isCached` method to check if the data was served from cache:

```php
use App\DataEntities\GetPostDataEntity;

$dataEntity = new GetPostDataEntity(1);
$response = $dataEntity->execute();

$response->isCached();
```

### HasRetries

The `HasRetries` plugin retries transient database failures such as deadlocks and timeouts.

```php
namespace App\DataEntities;

use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Plugins\HasRetries;
use Carbon\CarbonInterval;

class GetAllPostsDataEntity extends DataEntity
{
    use HasRetries;

    protected function maxRetryAttempts(): int
    {
        return 3;
    }

    protected function retryBackoff(): int|CarbonInterval
    {
        return CarbonInterval::milliseconds(50);
        // or: return 50; // milliseconds
    }
}
```

### Lazy Collection

Use the `#[UseLazyQuery]` attribute when the stored procedure can return a large (or unbounded) result set and you want to consume rows through a Laravel `LazyCollection` instead of loading everything via `data()` / `collect()` up front.

```php
namespace App\DataEntities;

use BitMx\DataEntities\Attributes\UseLazyQuery;
use BitMx\DataEntities\DataEntity;

#[UseLazyQuery]
class GetAllPostsDataEntity extends DataEntity
{
    public function resolveStoreProcedure(): string
    {
        return 'spListAllPost';
    }
}
```

With `#[UseLazyQuery]`, the executor runs the procedure through a database cursor. Rows are not fully materialised until you iterate `lazy()` or `stream()` on the response.

```php
use App\DataEntities\GetAllPostsDataEntity;

$dataEntity = new GetAllPostsDataEntity();
$response = $dataEntity->execute();
```

`lazy()` and `stream()` both return a Laravel `LazyCollection` over the same cursor. The difference is **memory** and **re-iteration**: `lazy()` remembers rows after the first pass so you can iterate again — on large datasets that means memory grows to the full result set size. Prefer `stream()` for large sets. If you hit *Lazy stream has already been consumed*, switch to `lazy()` when you need multiple passes on a moderate set, or call `execute()` again for a fresh cursor.

#### `lazy()` — re-iterable (remembers rows)

Default: `lazy()` / `lazy(remember: true)`. After the first pass, rows are kept in memory (`LazyCollection::remember()`), so you can iterate or transform the collection more than once without re-running the stored procedure.

```php
$posts = $response->lazy();

$count = $posts->count(); // first full pass; rows are now remembered
$titles = $posts->pluck('title'); // second pass; uses remembered rows (no extra DB round-trip)
```

#### `stream()` — single-pass (low memory)

Use `stream()` (or `lazy(remember: false)`, which is an alias) when you must avoid accumulating the full result set. Process one row at a time; a second iteration throws a `RuntimeException`.

```php
foreach ($response->stream() as $post) {
    // process one row at a time without keeping the full result set in memory
}
```

#### Risks of `lazy()` on large datasets

The name “lazy” does **not** mean permanently low memory. After the **first** iteration, `lazy()` remembers every row already seen. Memory can grow to the size of the entire result set — similar to calling `collect()` once you have walked the cursor.

Operations that force a full traversal (`count()`, `all()`, `toArray()`, multiple `foreach` loops, or pipelines that reuse the same collection) trigger that accumulation.

| Prefer | When |
| --- | --- |
| `stream()` | Massive exports, ETL, millions of rows, or any flow that only needs one pass |
| `lazy()` | Moderate result sets where you will traverse or transform more than once and want to avoid re-executing the SP |

Even with `stream()`, MySQL may still buffer the whole result set unless the connection is configured for unbuffered queries (see below).

#### If `stream()` was already consumed

A second iteration over the same stream raises:

`RuntimeException`: *Lazy stream has already been consumed and cannot be re-iterated. Use lazy() for a re-iterable collection.*

What to do:

1. **You need multiple passes on the same response** — use `$response->lazy()` from the start (rows are remembered after the first pass; watch memory on large sets).
2. **You already consumed `stream()` and need the data again** — call `execute()` again and obtain a new `stream()` / `lazy()`. The current response’s stream cannot be reset.
3. **`lazy(remember: false)`** — same single-pass limitation as `stream()`.

```php
// Wrong: second foreach fails
$stream = $response->stream();
foreach ($stream as $post) { /* ... */ }
foreach ($stream as $post) { /* RuntimeException */ }

// Right (moderate sets, multiple passes): use lazy() up front
foreach ($response->lazy() as $post) { /* first pass */ }
foreach ($response->lazy() as $post) { /* second pass; remembered */ }

// Right (large sets, need another pass): re-execute
$response = $dataEntity->execute();
foreach ($response->stream() as $post) { /* ... */ }
```

#### Restrictions

When using `#[UseLazyQuery]`, the response type only supports a collection. Combining it with `#[SingleItemResponse]` throws an exception.

`#[UseLazyQuery]` is also incompatible with output parameters. Lazy queries use a cursor over a single result set, so output values would be lost; combining both throws `InvalidLazyQueryException`.

#### MySQL and true streaming

On MySQL, PDO buffers result sets by default. For true streaming, configure the connection with unbuffered queries:

```php
'mysql' => [
    // ...
    'options' => [
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
    ],
],
```

## Data Transfer objects

Map stored-procedure rows into PHP objects and read them with `$response->dto()`.

### `#[MapTo]` — automatic mapping

`#[MapTo]` is an optional shortcut: the package reflects the DTO constructor and fills each parameter from the matching key in the response row (after aliases and accessors). Calling `$response->dto()` runs that mapping.

**DTO rules:**

- Prefer a constructor-based class; parameter names must match the row keys (`id`, `title`, …).
- Missing keys use the parameter’s default value when available, otherwise `null`.

#### Single item

Combine `#[SingleItemResponse]` with `#[MapTo(Dto::class)]`:

```php
namespace App\Data;

class PostData
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
    ) {
    }
}
```

```php
namespace App\DataEntities;

use App\Data\PostData;
use BitMx\DataEntities\Attributes\MapTo;
use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;

#[SingleItemResponse]
#[MapTo(PostData::class)]
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
```

```php
/** @var PostData $post */
$post = (new GetPostDataEntity(1))->execute()->dto();
```

#### Collection of DTOs

Pass Laravel’s `Illuminate\Support\Collection` (or a compatible subclass) as the second argument. Each row becomes a DTO and the result is wrapped in that collection class:

```php
namespace App\DataEntities;

use App\Data\PostData;
use BitMx\DataEntities\Attributes\MapTo;
use BitMx\DataEntities\DataEntity;
use Illuminate\Support\Collection;

#[MapTo(PostData::class, Collection::class)]
class GetPostsDataEntity extends DataEntity
{
    public function resolveStoreProcedure(): string
    {
        return 'spListPosts';
    }
}
```

```php
/** @var Collection<int, PostData> $posts */
$posts = (new GetPostsDataEntity())->execute()->dto();

$posts->each(fn (PostData $post) => /* ... */);
```

Empty result sets yield an empty collection. A single-item response with a collection class yields a collection of one DTO.

Any class constructible as `new $collectionClass($items)` works (for example `Illuminate\Database\Eloquent\Collection`).

### Manual `createDtoFromResponse()`

For nested objects, Spatie Data, custom transforms, or anything beyond constructor key matching, override `createDtoFromResponse()`. **A manual override always wins over `#[MapTo]`.**

```php
namespace App\DataEntities;

use App\Data\PostData;
use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\Response;

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

    public function createDtoFromResponse(Response $response): PostData
    {
        $data = $response->data();

        return new PostData(
            id: $data['id'],
            title: $data['title'],
            content: $data['content'],
        );
    }
}
```

```php
/** @var PostData $post */
$post = (new GetPostDataEntity(1))->execute()->dto();
```

## Debugging

You can call `dd` and `ddRaw` methods to debug the query sent to the database.

```php
use App\DataEntities\GetPostDataEntity;

$dataEntity = new GetPostDataEntity(1);

$dataEntity->dd();

$dataEntity->ddRaw();
```

## Testing

You can create integration tests for your Data Entities easily.

### Mocking the Data Entity

You can mock the Data Entity using the `DataEntity::fake` method. Assertions and helpers live on `DataEntity` itself (facade-style), so you do not need to capture a mock client.

```php
use App\DataEntities\GetPostDataEntity;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;

it('should get the post', function () {
    DataEntity::fake([
        GetPostDataEntity::class => MockResponse::make([
            'id' => 1,
            'title' => 'Post title',
            'content' => 'Post content',
        ]),
    ]);

    $dataEntity = new GetPostDataEntity(1);

    $response = $dataEntity->execute();

    $post = $response->dto();

    expect($post->id)->toBe(1);
    expect($post->title)->toBe('Post title');
    expect($post->content)->toBe('Post content');

    DataEntity::assertExecutedOnce(GetPostDataEntity::class);
    expect(DataEntity::recorded(GetPostDataEntity::class))->toHaveCount(1);
});
```

Reset mocks between tests (recommended in `tests/Pest.php`):

```php
afterEach(fn () => DataEntity::resetMock());
```

When using the `fake` method, the `execute` method will return the data specified in the `MockResponse::make` method and
won't execute the stored procedure.

### Fluent mock responses

```php
use BitMx\DataEntities\Responses\MockResponse;

MockResponse::make(['title' => 'New post'])->withOutput(['p_new_id' => 42]);
MockResponse::make(['title' => 'New post'], ['p_new_id' => 42]);
MockResponse::empty();
MockResponse::make()->withException(new RuntimeException('boom'));
// or
MockResponse::makeWithException(new RuntimeException('boom'));
```

### Assertions

You can use assertions to verify that the Data Entity was executed.

```php
use App\DataEntities\GetPostDataEntity;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Testing\RecordedExecution;

it('should get the post', function () {
    DataEntity::fake([
        GetPostDataEntity::class => MockResponse::make([
            'id' => 1,
            'title' => 'Post title',
            'content' => 'Post content',
        ]),
    ]);

    $dataEntity = new GetPostDataEntity(1);

    $response = $dataEntity->execute();

    DataEntity::assertExecuted(GetPostDataEntity::class);
});
```

Available assertions:

- **assertExecuted:** Assert that the Data Entity was executed.
- **assertNotExecuted:** Assert that the Data Entity was not executed.
- **assertNothingExecuted:** Assert that no Data Entity was executed.
- **assertExecutedCount:** Assert that the Data Entity was executed a specific number of times.
- **assertExecutedOnce:** Assert that the Data Entity was executed once.
- **assertTotalExecutedCount:** Assert the total number of Data Entity executions.
- **assertExecutedInOrder:** Assert Data Entities were executed in a specific order.
- **assertExecutedWith:** Assert that the Data Entity was executed with matching parameters (array subset, parameter closure, or `RecordedExecution` closure).

```php
DataEntity::assertExecutedWith(GetPostDataEntity::class, ['post_id' => 1]);
DataEntity::assertExecutedWith(GetPostDataEntity::class, fn (array $parameters) => $parameters['post_id'] > 0);
DataEntity::assertExecutedWith(
    GetPostDataEntity::class,
    fn (RecordedExecution $execution) => $execution->procedure === 'sp_get_post',
);
DataEntity::assertNothingExecuted();
DataEntity::assertTotalExecutedCount(1);
DataEntity::assertExecutedInOrder([GetPostDataEntity::class, OtherDataEntity::class]);
```

You can also fake with a closure, a sequence, merge more mocks, or set a fallback:

```php
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Responses\MockResponse;
use BitMx\DataEntities\Responses\MockResponseSequence;

DataEntity::fake([
    GetPostDataEntity::class => fn (PendingQuery $query) => MockResponse::make([
        'id' => $query->parameters()->get('post_id'),
    ]),
]);

DataEntity::fake([
    GetPostDataEntity::class => MockResponseSequence::make(
        MockResponse::make(['id' => 1]),
        MockResponse::make(['id' => 2]),
    )->whenEmpty(MockResponse::make(['id' => 0])),
]);

DataEntity::fake([
    GetPostDataEntity::class => MockResponse::make(['id' => 1]),
]);

DataEntity::mock([
    OtherDataEntity::class => MockResponse::empty(),
]);

DataEntity::fallback(MockResponse::make(['fallback' => true]));
```

### Using factories

You can use factories to create fake data for your Data Entities.

```php
namespace Tests\DataEntityFactories;

use BitMx\DataEntities\Factories\DataEntityFactory;

class PostDataEntityFactory extends DataEntityFactory
{
    /**
     * {@inheritDoc}
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->randomNumber(),
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];
    }
}
```

To create a factory you should extend the `DataEntityFactory` class and implement the `definition` method.

You can use the `faker` property to generate fake data.

Pass the factory (or its created data) to `MockResponse::make` inside `DataEntity::fake()`:

```php
use App\DataEntities\GetPostDataEntity;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;
use Tests\DataEntityFactories\PostDataEntityFactory;

it('should get the post', function () {
    DataEntity::fake([
        GetPostDataEntity::class => MockResponse::make(PostDataEntityFactory::new()),
    ]);

    $dataEntity = new GetPostDataEntity(1);

    $response = $dataEntity->execute();

    $post = $response->data();

    expect($post)->toHaveKeys(['id', 'title', 'content']);
});
```

You can also pass an array created with the `create` method:

```php
use App\DataEntities\GetPostDataEntity;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;
use Tests\DataEntityFactories\PostDataEntityFactory;

it('should get the post', function () {
    DataEntity::fake([
        GetPostDataEntity::class => MockResponse::make(PostDataEntityFactory::new()->create()),
    ]);

    $dataEntity = new GetPostDataEntity(1);

    $response = $dataEntity->execute();

    expect($response->data())->toHaveKeys(['id', 'title', 'content']);
});
```

You can also use the `count` method to create an array of fake data:

```php
use App\DataEntities\GetPostDataEntity;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;
use Tests\DataEntityFactories\PostDataEntityFactory;

it('should get a collection of posts', function () {
    DataEntity::fake([
        GetPostDataEntity::class => MockResponse::make(
            PostDataEntityFactory::new()->count(10)->asCollection()
        ),
    ]);

    $dataEntity = new GetPostDataEntity(1);

    $response = $dataEntity->execute();

    expect($response->data())->toHaveCount(10);
});
```

You can use the `state` method to change the default values of the factory:

```php
use App\DataEntities\GetPostDataEntity;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;
use Tests\DataEntityFactories\PostDataEntityFactory;

it('should get the post', function () {
    DataEntity::fake([
        GetPostDataEntity::class => MockResponse::make(
            PostDataEntityFactory::new()->state([
                'title' => 'Custom title',
            ])
        ),
    ]);

    $dataEntity = new GetPostDataEntity(1);

    $response = $dataEntity->execute();

    expect($response->data('title'))->toBe('Custom title');
});
```

Or create a new method in the factory to change the default values:

```php
namespace Tests\DataEntityFactories;

use BitMx\DataEntities\Factories\DataEntityFactory;

class PostDataEntityFactory extends DataEntityFactory
{
    /**
     * {@inheritDoc}
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->randomNumber(),
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];
    }

    public function withPublishedDate(): DataEntityFactory
    {
        return $this->state([
            'published_date' => now()->toDateTimeString(),
        ]);
    }
}
```

```php
use App\DataEntities\GetPostDataEntity;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;
use Tests\DataEntityFactories\PostDataEntityFactory;

it('should get the post', function () {
    DataEntity::fake([
        GetPostDataEntity::class => MockResponse::make(
            PostDataEntityFactory::new()->withPublishedDate()
        ),
    ]);

    $dataEntity = new GetPostDataEntity(1);

    $response = $dataEntity->execute();

    expect($response->data())->toHaveKey('published_date');
});
```

You can create a fake with an exception:

```php
use App\DataEntities\GetPostDataEntity;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Plugins\AlwaysThrowOnError;
use BitMx\DataEntities\Responses\MockResponse;

it('should throw when the stored procedure fails', function () {
    DataEntity::fake([
        GetPostDataEntity::class => MockResponse::makeWithException(new \Exception('Error')),
    ]);

    $dataEntity = new class(1) extends GetPostDataEntity
    {
        use AlwaysThrowOnError;
    };

    $dataEntity->execute();
})->throws(\Exception::class, 'Error');
```

### Response type

You can set the factory response type using the `responseType` method.

```php
namespace Tests\DataEntityFactories;

use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Factories\DataEntityFactory;

class PostDataEntityFactory extends DataEntityFactory
{
    /**
     * {@inheritDoc}
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->randomNumber(),
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
        ];
    }

    public function responseType(): ResponseType
    {
        return ResponseType::COLLECTION;
    }
}
```

You can also change the response type on the factory instance:

```php
use App\DataEntities\GetPostDataEntity;
use BitMx\DataEntities\DataEntity;
use BitMx\DataEntities\Responses\MockResponse;
use Tests\DataEntityFactories\PostDataEntityFactory;

it('should get a collection of posts', function () {
    DataEntity::fake([
        GetPostDataEntity::class => MockResponse::make(
            PostDataEntityFactory::new()->asCollection()
        ),
    ]);

    $dataEntity = new GetPostDataEntity(1);

    $response = $dataEntity->execute();

    expect($response->data())->toBeArray();
});
```

You can create a new factory using the artisan command.

```bash
php artisan make:data-entity-factory PostDataEntityFactory
```

This command will create a new factory in the `tests/DataEntityFactories` directory.

### Upgrading to version 4

## Key Changes

Version 4.0 introduces two primary breaking changes to simplify the `DataEntity` class.

### 1. Removal of the `responseType` Property

The `$responseType` property has been removed from the `DataEntity` class. By default, all responses now return a collection of items.

To specify that a response should return a single item, you must now use the `\BitMx\DataEntities\Attributes\SingleItemResponse` attribute directly on your `DataEntity` class.

**Example:**

```php
namespace App\DataEntities;

use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\DataEntity;

#[SingleItemResponse]
class GetPostDataEntity extends DataEntity
{
    public function __construct(
        protected int $postId,
    ) {
    }

    #[\Override]
    public function resolveStoreProcedure(): string
    {
        return 'spListPost';
    }

    #[\Override]
    protected function defaultParameters(): array
    {
        return [
            'post_id' => $this->postId,
        ];
    }
}
```

### 2. Removal of the `$method` Property

The `$method` property has also been removed from the base `DataEntity` class, as it is no longer utilized by the package.

## Automated Upgrade with Rector

To facilitate a smooth transition, we provide a set of Rector rules that can automate the upgrade process for your project.

Follow these steps to update your code automatically.

### Step 1: Install Rector

First, ensure you have Rector installed as a development dependency in your project.

```bash
composer require rector/rector --dev
```

### Step 2: Configure Rector

Next, create or update your `rector.php` configuration file in the root of your project to include the custom rules for this package.

```php
<?php

declare(strict_types=1);

use BitMx\DataEntities\Rector\RemoveMethodFromDataEntityRector;
use BitMx\DataEntities\Rector\ResponseTypePropertyToAttributeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        ResponseTypePropertyToAttributeRector::class,
        RemoveMethodFromDataEntityRector::class,
    ])
    ->withImportNames();
```

### Step 3: Run the Upgrade

Finally, execute the Rector `process` command, pointing it to the directory where your `DataEntity` classes are located.

```bash
vendor/bin/rector process app/DataEntities
```

Rector will analyze the files and apply the necessary modifications to align them with the new standards of version 4.0.
