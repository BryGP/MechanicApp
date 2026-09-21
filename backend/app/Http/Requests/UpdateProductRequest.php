<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateProductRequest
 * 
 * Validates updates to an existing spare part or service, ignoring the product's
 * own ID for unique SKU and Name constraints.
 * 
 * @package App\Http\Requests
 */
class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product') ? ($this->route('product')->id ?? $this->route('product')) : null;

        return [
            'name'        => "sometimes|string|max:255|unique:products,name,{$productId}",
            'sku'         => "sometimes|string|max:255|unique:products,sku,{$productId}",
            'description' => 'nullable|string|max:2000',
            'price'       => 'sometimes|numeric|gt:0',
            'stock'       => 'sometimes|integer|min:0',
            'min_stock'   => 'sometimes|integer|min:0',
            'is_service'  => 'sometimes|boolean',
        ];
    }

    /**
     * Custom error messages for attribute validation failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ya existe otra refacción o servicio registrado con este nombre.',
            'sku.unique'  => 'El código o SKU ingresado ya pertenece a otro producto o servicio.',
            'price.gt'    => 'El precio o tarifa debe ser mayor a $0.00.',
        ];
    }
}
