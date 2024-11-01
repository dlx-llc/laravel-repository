<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\App\Http\Controllers;

use Deluxetech\LaRepo\Facades\LaRepo;
use Deluxetech\LaRepo\Tests\App\Models\Product;
use Deluxetech\LaRepo\Eloquent\GenericRepository;
use Deluxetech\LaRepo\Tests\App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController
{
    private GenericRepository $productRepository;

    public function __construct()
    {
        $this->productRepository = new GenericRepository(Product::class);
    }

    public function index(): AnonymousResourceCollection
    {
        $records = LaRepo::getManyWithRequest(
            repository: $this->productRepository,
            criteria: ProductResource::getCriteria(),
            dataMapper: ProductResource::getDataMapper(),
        );

        return ProductResource::collection($records);
    }
}
