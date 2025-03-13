# Laravel Repository

[![Laravel Version](https://img.shields.io/badge/Laravel-10.x%2F12.x-blue)](https://laravel.com/)
[![Latest Stable Version](https://poser.pugx.org/dlx-llc/laravel-repository/v)](https://packagist.org/packages/dlx-llc/laravel-repository)
[![Total Downloads](https://poser.pugx.org/dlx-llc/laravel-repository/downloads)](https://packagist.org/packages/dlx-llc/laravel-repository)
[![Latest Unstable Version](https://poser.pugx.org/dlx-llc/laravel-repository/v/unstable)](https://packagist.org/packages/dlx-llc/laravel-repository)
[![License](https://poser.pugx.org/dlx-llc/laravel-repository/license)](https://packagist.org/packages/dlx-llc/laravel-repository)
[![PHP Version Require](https://poser.pugx.org/dlx-llc/laravel-repository/require/php)](https://packagist.org/packages/dlx-llc/laravel-repository)

## What is repository?
Repository mediates between the domain and data mapping layers using a collection-like interface for accessing domain objects.
In this package you will find its implementation for the Laravel framework.

## When to use it
In a large system with many domain object types and many possible queries, Repository reduces the amount of code needed to deal with all the querying that goes on. Repository promotes the Specification pattern (in the form of a criteria object), which encapsulates the query to be performed. Also, Repository can be really useful in situations with multiple data sources.

## Installation

Install this package using Composer:

```
composer require dlx-llc/laravel-repository
```

This package uses auto-discovery for its service provider. However, if you have auto-discovery disabled for this package, you will need to manually register the service provider:

```
Deluxetech\LaRepo\LaRepoServiceProvider::class
```

This package has its own exception and validation error message translations. You can override them by following the steps described in the <a href="https://laravel.com/docs/11.x/localization#overriding-package-language-files" target="_blank">Laravel documentation</a>.

There are also configurations that you might need to replace with your own. In that case, you can publish configurations using the following command:

```
php artisan vendor:publish --tag=larepo-config
```

## Versioning

This package was built to use with the latest version of Laravel, but it should work fine with versions >= 10.x.

## Usage

You can use the generic EloquentRepository class by passing the Laravel model class name to its constructor. There's also a LaRepo facade which provide you with a group of methods that may save you time writing some repetitive code.

```php
use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Eloquent\GenericRepository;
use Deluxetech\LaRepo\Contracts\RepositoryContract;

class UserController
{
    protected RepositoryContract $repo;

    public function __construct()
    {
        $this->repo = new GenericRepository(User::class);
    }

    public function index()
    {
        return LaRepo::getManyWithRequest($this->repo);
    }
}
```

# Running docker container

Build the image:
```
docker build -t dlx-llc-laravel-repository .
```

Use this command to (re)start the docker container:
```
docker rm -f dlx-llc-laravel-repository 2> /dev/null && \
docker run -itd \
    --name dlx-llc-laravel-repository \
    --mount type=bind,source="$(pwd)"/.,target=/home/larepo/app \
    --restart on-failure:5 \
    dlx-llc-laravel-repository
```
