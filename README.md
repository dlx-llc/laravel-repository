# Laravel Repository

Repository design pattern implementation for Laravel framework.

## Installation

Install this package using Composer:

```
composer require deluxetech/laravel-repository
```

This package uses auto-discovery for its service provider. However, if you have auto-discovery disabled for this package, you will need to manually register the service provider:

```
Deluxetech\LaRepo\LaRepoServiceProvider::class
```

### Versioning

This package was built to use with the latest version of Laravel, but it should work fine with versions >= 7.x.

## Usage

1) First of all, you should implement Deluxetech\LaRepo\Contracts\DtoContract interface in your data transfer objects (DTO) (e.g. Laravel resources). By carefully defining all relations and relation counts used by DTOs, you can be sure that all the necessary relationships will be eagerly loaded with data later.

```php
class JsonResource implements \Deluxetech\LaRepo\Contracts\DtoContract
```

2) Next, you'll need to use Deluxetech\LaRepo\Traits\FetchesRepositoryData trait in controller classes. Or you can simply use it in your base Controller class.

```php
use \Deluxetech\LaRepo\Traits\FetchesRepositoryData;
```

3) Then, you'll be able to fetch data like in the example below:

```php
$records = $this->getMany(
    repository: ReadonlyGenericRepository::make(User::query()),
    searchCriteria: SearchCriteriaFactory::createFromRequest(),
    pagination: PaginationFactory::createFromRequest(require: true),
    dto: UserResource::class
);
```
