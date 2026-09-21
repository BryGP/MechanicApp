<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ExpenseResource
 * 
 * Transforms Expense model attributes into a predictable JSON payload
 * for accounting and financial ledgers.
 * 
 * @package App\Http\Resources
 */
class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'concept'        => $this->concept,
            'category'       => $this->category,
            'amount'         => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'reference'      => $this->reference,
            'expense_date'   => $this->expense_date,
            'created_at'     => $this->created_at?->toISOString(),
            'updated_at'     => $this->updated_at?->toISOString(),
        ];
    }
}
