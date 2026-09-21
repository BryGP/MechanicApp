<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrderItemResource
 * 
 * Transforms OrderItem model attributes into a clean, predictable JSON contract
 * with optional nested Product relations.
 * 
 * @package App\Http\Resources
 */
class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'order_id'   => $this->order_id,
            'product_id' => $this->product_id,
            'quantity'   => (int) $this->quantity,
            'unit_price' => (float) $this->unit_price,
            'subtotal'   => (float) $this->subtotal,
            'product'    => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
