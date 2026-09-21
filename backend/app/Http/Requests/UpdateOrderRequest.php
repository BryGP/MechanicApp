<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateOrderRequest
 * 
 * Validates updates to customer details, vehicle, notes, or workflow status transitions.
 * 
 * @package App\Http\Requests
 */
class UpdateOrderRequest extends FormRequest
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
            'customer_name' => 'sometimes|string|max:255',
            'vehicle'       => 'sometimes|string|max:255',
            'notes'         => 'nullable|string|max:2000',
            'status'        => 'sometimes|string|in:open,in_progress,done,delivered',
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.in' => 'El estatus seleccionado no es válido (valores permitidos: open, in_progress, done, delivered).',
        ];
    }
}
