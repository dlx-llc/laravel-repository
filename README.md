# Laravel Repository

[![Laravel Version](https://img.shields.io/badge/Laravel-7.x%2F8.x%2F9.x-blue)](https://laravel.com/)
[![Latest Stable Version](http://poser.pugx.org/deluxetech/laravel-repository/v)](https://packagist.org/packages/deluxetech/laravel-repository) [![Total Downloads](http://poser.pugx.org/deluxetech/laravel-repository/downloads)](https://packagist.org/packages/deluxetech/laravel-repository) [![Latest Unstable Version](http://poser.pugx.org/deluxetech/laravel-repository/v/unstable)](https://packagist.org/packages/deluxetech/laravel-repository) [![License](http://poser.pugx.org/deluxetech/laravel-repository/license)](https://packagist.org/packages/deluxetech/laravel-repository) [![PHP Version Require](http://poser.pugx.org/deluxetech/laravel-repository/require/php)](https://packagist.org/packages/deluxetech/laravel-repository)

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

This package has its own exception and validation error message translations. You can override them by following the steps described in the <a href="https://laravel.com/docs/9.x/localization#overriding-package-language-files" target="_blank">Laravel documentation</a>.

There are also configurations that you might need to replace with your own. In that case, you can publish configurations using the following command:

```
php artisan vendor:publish --tag=larepo-config
```

## Versioning

This package was built to use with the latest version of Laravel, but it should work fine with versions >= 7.x.

## Usage

You can extend \Deluxetech\LaRepo\Eloquent\ReadonlyRepository for data read only or \Deluxetech\LaRepo\Eloquent\Repository for data full access. You'll only need to define the abstract getModel() method.

```php
use Deluxetech\LaRepo\Eloquent\Repository;

class UserRepository extends Repository
{
    /** @inheritdoc */
    protected function getModel(): string
    {
        return User::class;
    }
}
```

For simple cases when there's no much to do within the repository, you can use the generic \Deluxetech\LaRepo\Eloquent\GenericRepository repository by passing the Laravel model name into the class constructor.

```php
use Deluxetech\LaRepo\Eloquent\GenericRepository;

$userRepository = new GenericRepository(User::class);
```

There's also a Deluxetech\LaRepo\Traits\FetchesRepositoryData trait, which you can use in your controller classes. It will provide you with a group of methods that will save you time writing some repetitive code.

```php
use Deluxetech\LaRepo\Traits\FetchesRepositoryData;

class UserController
{
    use FetchesRepositoryData;

    protected $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function index()
    {
        return $this->getManyWithRequest($this->userRepo);
    }
}
```
