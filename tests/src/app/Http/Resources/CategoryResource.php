<?php

declare(strict_types=1);

namespace Deluxetech\LaRepo\Tests\App\Http\Resources;

use Illuminate\Http\Request;
use Deluxetech\LaRepo\Facades\LaRepo;
use Illuminate\Http\Resources\Json\JsonResource;
use Deluxetech\LaRepo\Contracts\CriteriaContract;
use Deluxetech\LaRepo\Contracts\DataMapperContract;

class CategoryResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }

    public static function getDataMapper(): DataMapperContract
    {
        return LaRepo::newDataMapper()
            ->set('createdAt', 'created_at')
            ->set('updatedAt', 'updated_at');
    }

    public static function getCriteria(): CriteriaContract
    {
        return LaRepo::newCriteria()
            ->setAttributes('id', 'name', 'description', 'created_at', 'updated_at');
    }
}
