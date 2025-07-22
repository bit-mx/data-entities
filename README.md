# Data Entities

Execute stored procedures in Laravel from Sqlserver without all the boilerplate code.

Table of Contents
=================

* [Introduction](#introduction)
* [Installation](#installation)
* [Setup](#setup)
* [Compatibility](#compatibility)
* [Getting Started](#getting-started)
    * [Create a Data Entity](#create-a-data-entity)
    * [Connection](#connection)
    * [Execute the Data Entity](#execute-the-data-entity)
    * [Mutators](#mutators)
        * [Available mutators](#available-mutators)
        * [Custom mutators](#custom-mutators)
    * [Accessors](#accessors)
        * [Available accessors](#available-accessors)
        * [Custom accessor](#custom-accessor)
    * [Response useful methods](#response-useful-methods)
        * [data](#data)
        * [Data with a key](#data-with-a-key)
        * [Data with a key and a default value](#data-with-a-key-and-a-default-value)
        * [Add data value](#add-data-value)
        * [Merge data](#merge-data)
        * [As object](#as-object)
        * [As collection](#as-collection)
        * [success](#success)
        * [failed](#failed)
        * [throw](#throw)
    * [Boot](#boot)
    * [Middlewares](#middlewares)
    * [Plugins](#plugins)
        * [AlwaysThrowOnError](#alwaysthrowonerror)
        * [HasCache](#hascache)
    * [Data Transfer objects](#data-transfer-objects)
    * [Debugging](#debugging)
    * [Testing](#testing)
        * [Mocking the Data Entity](#mocking-the-data-entity)
        * [Assertions](#assertions)
        * [Using factories](#using-factories)
        * [Response type](#response-type)
    * [Upgrading to version 4](#upgrading-to-version-4)

## Introduction

Data Entities is a library that allows you to execute stored procedures in Sqlserver easily. It is a wrapper around
the Laravel's DB Facade.

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

return [
    'database' => env('DATA_ENTITIES_CONNECTION', 'sqlsrv'),
];
```

## Compatibility

This package is compatible with Laravel 11.x and above.

Due laravel 11 requires php 8.2, this package is compatible with php 8.2 and above.

## Getting Started

### Create a Data Entity

To create a Data Entity, you need to extend the DataEntity class and implement the resolveStoreProcedure method with the
name of the stored procedure you want to execute.

You can also override the defaultParameters method to set the default parameters for the stored procedure.

```php
namespace App\DataEntities;

use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Collection;

class GetAllPostsDataEntity extends DataEntity
{
    
    public function __construct(
        protected int $authorId,
    ) 
    {
    
    }
    
    #[\Override]
    public function resolveStoreProcedure(): string
    {
        return 'spListAllPost';
    }

    #[\Override]
    public function defaultParameters(): array
    {
        return [
            'author_id' => $this->authorId,
        ];
    } 
}
```

You can also use the parameters method to set the parameters for the stored procedure.

```php

use App\DataEntities\GetAllPostsDataEntity;

$dataEntity = new GetAllPostsDataEntity(1);

$dataEntity->parameters()->add('tag', 'laravel');
```

By default, the Data Entity will return a Response with a collection of records. You can change this by setting the
php attribute `SingleItemResponse`. This way, you can return a single record instead of a collection.

```php
namespace App\DataEntities;

use DataEntities\DataEntity;
use BitMx\DataEntities\Attributes\SingleItemResponse;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Collection;

#[SingleItemResponse]
class GetAllPostsDataEntity extends DataEntity
{
    
    public function __construct(
        protected int $authorId,
    ) 
    {
    
    }
    
    #[\Override]
    public function resolveStoreProcedure(): string
    {
        return 'spListAllPost';
    }

    #[\Override]
    public function defaultParameters(): array
    {
        return [
            'author_id' => $this->authorId,
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

### Connection

You can set the connection name overriding the resolveDatabaseConnection method.

```php
namespace App\DataEntities;

use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;

class GetAllPostsDataEntity extends DataEntity
{
    ...
    
    #[\Override]
    public function resolveDatabaseConnection(): string
    {
        return 'sqlsrv';
    }
}
```

### Execute the Data Entity

To execute the Data Entity, you need to call the execute method on the Data Entity instance.

```php  
use App\DataEntities\GetAllPostsDataEntity;

$dataEntity = new GetAllPostsDataEntity(1);

$response = $dataEntity->execute();

$data = $response->data();
``` 

The execute method returns a Response object that contains the data returned by the stored procedure.

### Mutators

You can use the mutators method to transform the parameters before sending them to the Store Procedure.

```php

namespace App\DataEntities;

use Carbon\Carbon;use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;

class GetAllPostsDataEntity extends DataEntity
{
    ...
    
    #[\Override]
    public function defaultParameters(): array
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
  : Converts the value to a datetime string using the specified format.
  You can pass a format as an argument to the cast.
  Examples:

    - `datetime` Returns Y-m-d H:i:s
    - `datetime:Y-m-d`
    - `datetime:H:i:s`
    - `datetime:Y-m-d H:i:s`
- **date:**
  : Converts the value to a date `Y-m-d`

- **bool:**
  : Converts the value to a boolean in int.
  Example: If the value is true, it will be converted to 1, and if it is false, it will be converted to 0.

- **int:**
  : Converts the value to an integer.

- **float:**
  : Converts the value to a float. You can pass the number of decimals as an argument to the cast.
  Example:

      - `float` Returns a float with 2 decimals.
      - `float:4` Returns a float with 4 decimals.
      - `float:0` Returns an integer.

- **string:**
  : Converts the value to a string.

- **json:**
  : Converts the value to a json string.
  : Example:

  : - if you pass an array, it will be converted to a json string.
  - [1, 2,4] will be converted to "[1,2,4]"
  - ['name' => 'John'] will be converted to '{"name":"John"}'
  - You can pass the JSON options as an argument to the cast.
  - `'json:'. JSON_PRETTY_PRINT` will return the json string with the JSON_PRETTY_PRINT option.

### Custom mutators

You can create custom mutators by implementing the Mutable interface.

```php
namespace BitMx\DataEntities\Mutators;

use BitMx\DataEntities\Contracts\Mutable;

class CustomMutator implements Mutable
{
    /**
     * {@inheritDoc}
     */
    public function transform(string $key, mixed $value, array $parameters): mixed
    {
        
    }
}
```

You can create a new cast using the artisan command.

```bash
php artisan make:data-entity-mutator CustomMutator
```

### Accessors

You can use the accessors method to transform the data returned by the stored procedure.

```php

namespace App\DataEntities;

use Carbon\Carbon;use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;

class GetAllPostsDataEntity extends DataEntity
{
    ...
    
    #[\Override]
    public function defaultParameters(): array
    {
        return [
            'date' => Carbon::now(),
        ];
    } 
    
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

This will transform the contact_id key to an integer before returning the data.

#### Available accessors

- **datetime:**
  : Converts the value to a DateTime instance.

- **datetime_immutable:**
  : Converts the value to a DateTimeImmutable instance.

- **bool:**
  : Converts the value to a boolean
  Example: If the value is 1, it will be converted true

- **int:**
  : Converts the value to an integer.

- **float:**
  : Converts the value to a float

- **string:**
  : Converts the value to a string.

- **array:**
  : Converts the value from a json string to an array.
  -
  - **object:**
  : Converts the value from a json string to an object.

- **collection:**
  : Converts the value from a json string to a Laravel Collection.

### Custom accessor

You can create custom accessors by implementing the Accessable interface.

```php
namespace BitMx\DataEntities\Accessors;

use BitMx\DataEntities\Contracts\Accessable;

class CustomAccessor implements Accessable
{
    /**
     * {@inheritDoc}
     */
    public function get(string $key, mixed $value, array $data): mixed
    {
        
    }
}
```

You can create a new accessor using the artisan command.

```bash
php artisan make:data-entity-accessor CustomAccessor
```

## Response useful methods

The Response object has some useful methods to work with the data returned by the stored procedure.

### data

The data method returns the data returned by the stored procedure as an array.

```php
$data = $response->data();
```

### Data with a key

You can get the data with a key

```php
$data = $response->data('key');
```

### Data with a key and a default value

You can get the data with a key and a default value

```php  
$data = $response
    ->data('key', 'default value');
```

### Add data value

You can add a value to the data array

```php
$response->addData('key', 'value');
```

You can add as an array to the data array

```php
$response->addData(['key' => 'value']);
```

### Merge data

You can merge an array with the data array

```php
$response->mergeData(['key' => 'value']);
```

### As object

You can get the data as an object

```php
$data = $response->object();
```

### As collection

You can get the data as a collection

```php

$data = $response->collect();
```

### success

The success method returns true if the stored procedure was executed successfully, and false otherwise.

```php

if ($response->success()) {
    // The stored procedure was executed successfully
} else {
    // There was an error executing the stored procedure
}
```

### failed

The fail method returns true if the stored procedure failed, and false otherwise.

```php

if ($response->failed()) {
    // There was an error executing the stored procedure
} else {
    // The stored procedure was executed successfully
}
``` 

### throw

By default, the Response object won't throw an exception if the stored procedure fails. You can throw an exception
manually
using the throw method.

```php

$response->throw();
```

## Boot

You can use the boot method to execute code before and after the stored procedure is executed.

```php
namespace App\DataEntities;

use BitMx\DataEntities\PendingQuery;
use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Collection;


class GetAllPostsDataEntity extends DataEntity
{
    
    
    ...
    
    #[\Override]
    public function boot(PendingQuery $pendingQuery): void
    {
        $pendingQuery->parameters()->all('tag', 'laravel');
    }
    
}
```

### Traits

You can use traits to add functionality to your Data Entities. Add the method bootTrait to the Data Entity to use the
trait.

```php
trait Taggable
{
    public function bootTaggable(PendingQuery $pendingQuery): void
    {
        $pendingQuery->parameters()->add('tag', 'laravel');
    }
}
```

The bootTaggable method will be called before the stored procedure is executed.

## Middlewares

You can use middlewares to execute code before and after the stored procedure is executed.

```php
namespace App\DataEntities;

use BitMx\DataEntities\PendingQuery;
use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Collection;


class GetAllPostsDataEntity extends DataEntity
{
    
    
    ...
    
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

You can alse use a invokable class as a middleware. This class should implement the QueryMiddleware or
ResponseMiddleware interface.

```php
use BitMx\DataEntities\Contracts\QueryMiddleware;

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
    public function __invoke(Response $pendingQuery): Response
    {
        $response->addData('tag', 'laravel');
        
        return $response;
    }
}
```

```php
namespace App\DataEntities;

use BitMx\DataEntities\PendingQuery;
use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Collection;


class GetAllPostsDataEntity extends DataEntity
{
    
    
    ...
    
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

The AlwaysThrowOnError plugin will throw an exception if the stored procedure fails.

```php

namespace App\DataEntities;

use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Plugins\AlwaysThrowOnError;
use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Collection;


class GetAllPostsDataEntity extends DataEntity
{
    use AlwaysThrowOnError;

    protected ?Method $method = Method::SELECT;
    
    
    
    ...
   
}
```

### HasCache

The HasCache plugin will cache the data returned by the stored procedure.

Data Entity shloud implement the Cacheable interface.

```php
namespace App\DataEntities;

use BitMx\DataEntities\Contracts\Cacheable;
use BitMx\DataEntities\PendingQuery;
use BitMx\DataEntities\Plugins\AlwaysThrowOnError;
use BitMx\DataEntities\Plugins\HasCache;use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Collection;


class GetAllPostsDataEntity extends DataEntity implements Cacheable
{
    use HasCache;

    
    
    ...
    
    public function cacheExpiresAt(): \DateTimeInterface {
        return now()->addMinutes(10);
    }
   
}
```

You can invalidate the cache using the invalidateCache method.

```php
use App\DataEntities\GetPostDataEntity;

$dataEntity = new GetPostDataEntity(1);

$post = $response->invalidateCache();
$response = $dataEntity->execute();

```

Or you can disable temporarily the cache using the disableCaching method.

```php
use App\DataEntities\GetPostDataEntity;

$dataEntity = new GetPostDataEntity(1);

$post = $response->disableCaching();
$response = $dataEntity->execute();

```

Response object will have a isCached method to check if the data was cached.

```php
use App\DataEntities\GetPostDataEntity;

$dataEntity = new GetPostDataEntity(1);

$post = $response->disableCaching();
$response = $dataEntity->execute();

$response->isCached(); //

```

### Lazy Collection

If you want to return a LazyCollection instance, you can use the UseLazyQuery attribute.

```php
namespace App\DataEntities;

use BitMx\DataEntities\Attributes\UseLazyQuery;
use BitMx\DataEntities\Contracts\Cacheable;
use BitMx\DataEntities\PendingQuery;
use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Collection;

#[UseLazyQuery]
class GetAllPostsDataEntity extends DataEntity
{
    public function resolveStoreProcedure(): string
    {
        return 'spListAllPost';
    }
}
```

This plugin will return a LazyCollection instance when lazy method is called on the Response object.

```php  
use App\DataEntities\GetAllPostsDataEntity;
$dataEntity = new GetAllPostsDataEntity(1);
$response = $dataEntity->execute();
$posts = $response->lazy();
```

#### Note

When using the UseLazyQuery attribute, the response type only supports COLLECTION. If you try to use SINGLE, it will
throw an exception.

## Data Transfer objects

You can use Data Transfer objects to map the data returned by the stored procedure to a PHP object.

```php
namespace App\Data;


class PostDat
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
    ) 
    {
    
    }
}
```

```php
namespace App\DataEntities;

use DataEntities\DataEntity;
use BitMx\DataEntities\Enums\Method;
use BitMx\DataEntities\Enums\ResponseType;
use BitMx\DataEntities\Responses\Response;
use Illuminate\Support\Collection;
use App\Data\PostData;

class GetPostDataEntity extends DataEntity
{
    protected ?Method $method = Method::SELECT;
    
    
    
    public function __construct(
        protected int $postId,
    ) 
    {
    
    }
    
    #[\Override]
    public function resolveStoreProcedure(): string
    {
        return 'spListPost';
    }

    #[\Override]
    public function defaultParameters(): array
    {
        return [
            'post_is' => $this->postId,
        ];
    } 
    
    public function createDtoFromResponse(Response $response): PostData
    {
        $data = $response->getData();
        
        return new PostData(
            id: $data['id'],
            title: $data['title'],
            content: $data['content'],
        );
    }
}

```

You can get the dto from the response using the dto method.

```php
use App\DataEntities\GetPostDataEntity;

$dataEntity = new GetPostDataEntity(1);

$response = $dataEntity->execute();

/** @var PostData $post */
$post = $response->dto();
```

## Debugging

You cal call dd and ddRaw methods to debug the query sent to the database.

```php

use App\DataEntities\GetPostDataEntity;

$dataEntity = new GetPostDataEntity(1);

$dataEntity->dd();

$dataEntity->ddRaw();
```

## Testing

You can create integration tests for your Data Entities easily.

### Mocking the Data Entity

You can mock the Data Entity using the DataEntity::fake method.

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
});
```

When using the fake method, the execute method will return the data specified in the MockResponse::make method and
won't execute the stored procedure.

### Assertions

You can use the assert method to assert that the Data Entity was executed.

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

    DataEntity::assertExecuted(GetPostDataEntity::class);
});
```

#### Assertions

You can use the following assertions:

- **assertExecuted:** Assert that the Data Entity was executed.
- **assertNotExecuted:** Assert that the Data Entity was not executed.
- **assertExecutedCount:** Assert that the Data Entity was executed a specific number of times.
- **assertExecutedOnce:** Assert that the Data Entity was executed once.

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

To create a factory you should extend the DataEntityFactory class and implement the definition method.

You can use the faker property to generate fake data.

```php

use App\DataEntities\GetPostDataEntity;
use Tests\DataEntityFactories\PostDataEntityFactory;
use BitMx\DataEntities\Responses\MockResponse;

it('should get the post', function () {
    $dataEntity = MockResponse::make(PostDataEntityFactory::new());

    $response = $dataEntity->execute();

    $post = $response->dto();

    expect($post->id)->toBe(1);
    expect($post->title)->toBe('Post title');
    expect($post->content)->toBe('Post content');
});
```

You can pass directly the factory to the MockResponse::make method. or you can create an array
with the create method.

```php

use App\DataEntities\GetPostDataEntity;
use Tests\DataEntityFactories\PostDataEntityFactory;
use BitMx\DataEntities\Responses\MockResponse;

it('should get the post', function () {
    $dataEntity = MockResponse::make(PostDataEntityFactory::new()->create());

    $response = $dataEntity->execute();

    $post = $response->dto();

    expect($post->id)->toBe(1);
    expect($post->title)->toBe('Post title');
    expect($post->content)->toBe('Post content');
});
```

You can also use the count method to create an array of fake data.

```php

use App\DataEntities\GetPostDataEntity;
use Tests\DataEntityFactories\PostDataEntityFactory;
use BitMx\DataEntities\Responses\MockResponse;

it('should get the post', function () {
    $dataEntity = MockResponse::make(PostDataEntityFactory::new()->count(10));

    $response = $dataEntity->execute();

    $posts = $response->dto();

    expect($posts)->toHaveCount(10);
});
```

You can use the state method to change the default values of the factory.

```php

use App\DataEntities\GetPostDataEntity;
use Tests\DataEntityFactories\PostDataEntityFactory;
use BitMx\DataEntities\Responses\MockResponse;

it('should get the post', function () {
    $dataEntity = MockResponse::make(PostDataEntityFactory::new()->state([
        'title' => 'Custom title',
    ]));

    $response = $dataEntity->execute();

    $post = $response->dto();

    expect($post->title)->toBe('Custom title');
});
```

Or create a new method in the factory to change the default values.

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
    
    public function withPublishedDate(array $state): DataEntityFactory
    {
        return $this->state([
            'published_date' => now(),
        ]);
    }
}
```

```php

use App\DataEntities\GetPostDataEntity;
use Tests\DataEntityFactories\PostDataEntityFactory;
use BitMx\DataEntities\Responses\MockResponse;

it('should get the post', function () {
    $dataEntity = MockResponse::make(PostDataEntityFactory::new()->withPublishedDate());

    $response = $dataEntity->execute();

    $post = $response->dto();

    expect($post->published_date)->toBe(now());
});
```

You can create a fake with an exception

```php

use App\DataEntities\GetPostDataEntity;
use Tests\DataEntityFactories\PostDataEntityFactory;
use BitMx\DataEntities\Responses\MockResponse;

it('should get the post', function () {
    $dataEntity = MockResponse::makeWithException(new \Exception('Error'));

    $response = $dataEntity->execute();
})
    ->throws(\Exception::class, 'Error');
```

### Response type

You can set the response type using the responseType method.

```php
namespace Tests\DataEntityFactories;

use BitMx\DataEntities\Enums\ResponseType;use BitMx\DataEntities\Factories\DataEntityFactory;

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
    
    public function responseType() : ResponseType{
         return ResponseType::COLLECTION;
    }
}
```

You can change the response type on MockResponse

```php

use App\DataEntities\GetPostDataEntity;
use Tests\DataEntityFactories\PostDataEntityFactory;
use BitMx\DataEntities\Responses\MockResponse;

it('should get the post', function () {
    $dataEntity = MockResponse::make(PostDataEntityFactory::new()->asCollection());

    $response = $dataEntity->execute();

    ....

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
    public function defaultParameters(): array
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
