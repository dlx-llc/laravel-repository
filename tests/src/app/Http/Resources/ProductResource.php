<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\App\Http\Resources;

use Illuminate\Http\Request;
use Deluxetech\LaRepo\Facades\LaRepo;
use Illuminate\Http\Resources\Json\JsonResource;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;

class ProductResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => CategoryResource::make($this->category),
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'sku' => $this->sku,
            'labels' => LabelResource::collection($this->labels),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }

    public static function getDataMapper(): DataMapperContract
    {
        return LaRepo::newDataMapper()
            ->set('category', 'category', CategoryResource::getDataMapper())
            ->set('category.id', 'category_id')
            ->set('createdAt', 'created_at')
            ->set('updatedAt', 'updated_at');
    }

    public static function getCriteria(): CriteriaContract
    {
        return LaRepo::newCriteria()
            ->addRelation('category', CategoryResource::getCriteria())
            ->addRelation('labels');
    }
}
