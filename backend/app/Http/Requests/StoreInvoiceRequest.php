<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreInvoiceRequest
 * 
 * Validates fiscal customer information according to SAT CFDI 4.0 rules.
 * 
 * @package App\Http\Requests
 */
class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'order_id'                 => 'nullable|exists:orders,id',
            'rfc_receptor'             => [
                'required',
                'string',
                'regex:/^([A-ZÑ&]{3,4})(\d{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[12]\d|3[01]))([A-Z\d]{2}[A\d])$/i',
            ],
            'razon_social_receptor'    => 'required|string|min:3|max:255',
            'regimen_fiscal_receptor'  => 'required|string|max:10',
            'codigo_postal_receptor'   => 'required|string|size:5|regex:/^\d{5}$/',
            'uso_cfdi'                 => 'required|string|max:10',
            'forma_pago'               => 'required|string|max:5',
            'metodo_pago'              => 'required|in:PUE,PPD',
            'custom_concept'           => 'nullable|array',
            'custom_concept.descripcion'    => 'required_with:custom_concept|string|max:255',
            'custom_concept.cantidad'       => 'required_with:custom_concept|integer|min:1',
            'custom_concept.valor_unitario' => 'required_with:custom_concept|numeric|min:1',
        ];
    }

    /**
     * Custom validation messages in Spanish.
     */
    public function messages(): array
    {
        return [
            'rfc_receptor.required'            => 'El RFC del cliente es obligatorio.',
            'rfc_receptor.regex'               => 'El formato del RFC no es válido según los lineamientos del SAT (12 caracteres para Persona Moral o 13 para Persona Física).',
            'razon_social_receptor.required'   => 'La Razón Social o Nombre Fiscal del receptor es obligatoria.',
            'regimen_fiscal_receptor.required' => 'Debes seleccionar el Régimen Fiscal del receptor.',
            'codigo_postal_receptor.required'  => 'El Código Postal fiscal es obligatorio.',
            'codigo_postal_receptor.regex'     => 'El Código Postal debe constar exactamente de 5 dígitos numéricos.',
            'uso_cfdi.required'                => 'Debes seleccionar el Uso de CFDI.',
            'forma_pago.required'              => 'Debes indicar la forma de pago.',
            'metodo_pago.required'             => 'Debes indicar el método de pago (PUE o PPD).',
            'order_id.exists'                  => 'La orden seleccionada no existe en la base de datos.',
        ];
    }

    /**
     * Configure additional validation hooks to enforce fiscal business rules.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('order_id')) {
                $existingInvoice = \App\Models\Invoice::where('order_id', $this->order_id)
                    ->where('status', 'vigente')
                    ->first();

                if ($existingInvoice) {
                    $folio = ($existingInvoice->series ?: 'FAC') . '-' . str_pad($existingInvoice->folio, 4, '0', STR_PAD_LEFT);
                    $validator->errors()->add(
                        'order_id',
                        "La Orden #{$this->order_id} ya se encuentra facturada con el comprobante {$folio} (UUID: {$existingInvoice->uuid}) con estatus VIGENTE. Para emitir una nueva factura de este servicio, primero debes cancelar la anterior ante el SAT."
                    );
                }
            }
        });
    }
}
