<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ProductResource
 * 
 * Transforms Product model attributes into a clean, predictable JSON contract
 * for frontend consumption.
 * 
 * @package App\Http\Resources
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'sku'         => $this->sku,
            'description' => $this->description,
            'price'       => (float) $this->price,
            'stock'       => (int) $this->stock,
            'min_stock'   => (int) $this->min_stock,
            'is_service'  => (bool) $this->is_service,
            'created_at'  => $this->created_at?->toISOString(),
            'updated_at'  => $this->updated_at?->toISOString(),
        ];
    }
}
