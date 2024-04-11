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
* [Response useful methods](#response-useful-methods)
    * [getData](#getdata)
    * [success](#success)
    * [failed](#failed)
    * [throw](#throw)
* [Data Transfer objects](#data-transfer-objects)
* [Debugging](#debugging)
* [Testing](#testing)
    * [Mocking the Data Entity](#mocking-the-data-entity)
    * [Assertions](#assertions)
    * [Using factories](#using-factories)

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

This package is compatible with Laravel 10.x and above.

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
    protected ?Method $method = Method::SELECT;
    
    protected ?ResponseType $responseType = ResponseType::SINGLE;
    
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

The ResponseType enum has two options: SINGLE and COLLECTION.

SINGLE is used when the stored procedure returns a single row, and COLLECTION is used when the stored procedure returns
multiple rows.

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

$data = $response->getData();
``` 

The execute method returns a Response object that contains the data returned by the stored procedure.

## Response useful methods

The Response object has some useful methods to work with the data returned by the stored procedure.

### getData

The getData method returns the data returned by the stored procedure as an array.

```php
$data = $response->getData();
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

By default, the Response object will throw an exception if the stored procedure fails. You can throw the exception
manually
using the throw method.

```php

$response->throw();
```

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
    
    protected ?ResponseType $responseType = ResponseType::SINGLE;
    
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

You can create a new factory using the artisan command.

```bash
php artisan make:data-entity-factory PostDataEntityFactory
```

This command will create a new factory in the `tests/DataEntityFactories` directory.
