<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Class StoreOrderRequest
 * 
 * Validates customer credentials, vehicle information, line items array,
 * and guards against rapid duplicate order submissions.
 * 
 * @package App\Http\Requests
 */
class StoreOrderRequest extends FormRequest
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
            'customer_name'      => 'required|string|max:255',
            'vehicle'            => 'required|string|max:255',
            'notes'              => 'nullable|string|max:2000',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty'        => 'required|integer|min:1',
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
            'customer_name.required' => 'El nombre del cliente es obligatorio para registrar la orden.',
            'vehicle.required'       => 'El modelo o descripción del vehículo es obligatorio.',
            'items.required'         => 'Debes incluir al menos una refacción o servicio.',
            'items.min'              => 'Debes incluir al menos una refacción o servicio.',
            'items.*.product_id.exists' => 'Una de las piezas o servicios seleccionados no existe en el catálogo.',
            'items.*.qty.min'        => 'La cantidad solicitada debe ser al menos 1 unidad.',
        ];
    }

    /**
     * Configure the validator instance with an after-validation hook
     * to prevent rapid duplicate double-click submissions.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $customer = trim((string) $this->input('customer_name'));
            $vehicle  = trim((string) $this->input('vehicle'));

            if (!empty($customer) && !empty($vehicle)) {
                $recentDupe = Order::where('customer_name', $customer)
                    ->where('vehicle', $vehicle)
                    ->where('created_at', '>=', now()->subSeconds(30))
                    ->first();

                if ($recentDupe) {
                    $validator->errors()->add(
                        'duplicate',
                        'Ya se registró una orden con este mismo cliente y vehículo hace unos momentos.'
                    );
                }
            }

            // Validación estricta de stock físico disponible en almacén
            $items = $this->input('items', []);
            if (is_array($items)) {
                $requestedQuantities = [];
                foreach ($items as $it) {
                    if (!empty($it['product_id']) && !empty($it['qty'])) {
                        $pid = (int) $it['product_id'];
                        $requestedQuantities[$pid] = ($requestedQuantities[$pid] ?? 0) + (int) $it['qty'];
                    }
                }

                if (!empty($requestedQuantities)) {
                    $products = Product::whereIn('id', array_keys($requestedQuantities))->get()->keyBy('id');
                    foreach ($requestedQuantities as $pid => $totalQty) {
                        $product = $products->get($pid);
                        if ($product && empty($product->is_service)) {
                            if ($totalQty > $product->stock) {
                                $disponible = max(0, (int) $product->stock);
                                $validator->errors()->add(
                                    'items',
                                    "Stock insuficiente para \"{$product->name}\" (SKU: {$product->sku}). Solicitaste {$totalQty} pzas, pero solo hay {$disponible} disponibles en almacén."
                                );
                            }
                        }
                    }
                }
            }
        });
    }
}
