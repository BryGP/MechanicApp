<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Class StoreExpenseRequest
 * 
 * Validates operational disbursement entries, strictly enforcing positive monetary amounts,
 * valid category tags, standardized dates, and preventing duplicate accounting logs.
 * 
 * @package App\Http\Requests
 */
class StoreExpenseRequest extends FormRequest
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
            'concept'        => 'required|string|max:255',
            'category'       => 'required|string|max:100',
            'amount'         => 'required|numeric|min:0.01|max:1000000',
            'payment_method' => 'nullable|string|max:50',
            'reference'      => 'nullable|string|max:100',
            'expense_date'   => 'required|date',
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'concept.required'      => 'El concepto del gasto es obligatorio.',
            'category.required'     => 'La categoría contable es obligatoria.',
            'amount.required'       => 'El monto del gasto es obligatorio.',
            'amount.min'            => 'El importe debe ser mayor a $0.00.',
            'amount.max'            => 'El importe máximo permitido por asiento contable es de $1,000,000.00 MXN (Control interno anti-error de dedo).',
            'expense_date.required' => 'La fecha de aplicación contable es obligatoria.',
            'expense_date.date'     => 'El formato de fecha no es válido.',
        ];
    }

    /**
     * Configure the validator instance with an after-validation hook
     * to prevent duplicate expense entries.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $concept = trim((string) $this->input('concept'));
            $amount  = (float) $this->input('amount');
            $date    = (string) $this->input('expense_date');

            if (!empty($concept) && $amount > 0 && !empty($date)) {
                $duplicate = Expense::where('concept', $concept)
                    ->where('amount', $amount)
                    ->where('expense_date', $date)
                    ->exists();

                if ($duplicate) {
                    $validator->errors()->add(
                        'duplicate',
                        'Ya existe un egreso registrado con este mismo concepto, monto y fecha contable.'
                    );
                }
            }
        });
    }
}
