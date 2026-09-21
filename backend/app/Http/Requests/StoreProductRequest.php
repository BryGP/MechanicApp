<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreProductRequest
 * 
 * Validates the payload for registering a new physical spare part or workshop service.
 * Enforces unique SKU and name constraints and ensures positive pricing.
 * 
 * @package App\Http\Requests
 */
class StoreProductRequest extends FormRequest
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
        return [
            'name'        => 'required|string|max:255|unique:products,name',
            'sku'         => 'required|string|max:255|unique:products,sku',
            'description' => 'nullable|string|max:2000',
            'price'       => 'required|numeric|gt:0',
            'stock'       => 'nullable|integer|min:0',
            'min_stock'   => 'nullable|integer|min:0',
            'is_service'  => 'nullable|boolean',
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
            'name.unique'    => 'Ya existe una refacción o servicio registrado con este mismo nombre.',
            'name.required'  => 'El nombre de la refacción o servicio es obligatorio.',
            'sku.unique'     => 'El código o clave SKU ingresado ya está registrado en otro producto o servicio.',
            'sku.required'   => 'El código SKU es obligatorio.',
            'price.required' => 'El precio o tarifa es obligatorio.',
            'price.gt'       => 'El precio o tarifa debe ser mayor a $0.00.',
        ];
    }
}
