<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================================
 * CLASE: OrderController
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Orquesta el flujo neurálgico del taller automotriz: la gestión integral de órdenes 
 * de servicio por vehículo y cliente. Administra el alta de órdenes, cálculo de 
 * cotizaciones, consumo de refacciones de almacén, seguimiento del estatus 
 * de reparación ('open' -> 'in_progress' -> 'done' -> 'delivered') y liquidación final.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Separación de validaciones en Form Requests (StoreOrderRequest, UpdateOrderRequest).
 * - Transformación estandarizada mediante OrderResource (DTO) con partidas anidadas.
 * - Transacciones Atómicas ACID (DB::transaction):
 *   Garantiza que la creación de la cabecera de orden, inserción de cada ítem, 
 *   congelamiento de precios y deducción de existencias en almacén ocurran de forma 
 *   atómica. Si una sola pieza no existe o falla la base de datos, se ejecuta un 
 *   Rollback absoluto, previniendo inconsistencias de stock.
 * - Snapshot Pricing (Congelamiento de Precios Históricos).
 * - Eager Loading Relacional multinivel ('with('items.product')').
 * - Discriminación de Mano de Obra vs Refacciones.
 *
 * MAPEO DE RUTAS (API Resource en routes/api.php):
 * - GET    /api/orders      -> index()   (Listar órdenes con partidas)
 * - POST   /api/orders      -> store()   (Creación transaccional con deducción)
 * - GET    /api/orders/{id} -> show()    (Detalle de una orden específica)
 * - PUT    /api/orders/{id} -> update()  (Actualización de estatus/vehículo)
 * - DELETE /api/orders/{id} -> destroy() (Eliminación en cascada)
 * ============================================================================
 */
class OrderController extends Controller
{
    // =========================================================================
    // SECCIÓN 1: CONSULTA DE ÓRDENES Y CARGA EAGER LOADING
    // =========================================================================

    /**
     * Consulta las órdenes más recientes con precarga ansiosa (Eager Loading)
     * de sus partidas y el producto asociado, transformadas con OrderResource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return OrderResource::collection(Order::with('items.product')->latest()->get());
    }

    // =========================================================================
    // SECCIÓN 2: CREACIÓN TRANSACCIONAL Y DEDUCCIÓN DE STOCK
    // =========================================================================

    /**
     * Crea una orden de trabajo, calcula totales y descuenta refacciones en transacción atómica.
     *
     * @param  \App\Http\Requests\StoreOrderRequest  $request
     * @return \App\Http\Resources\OrderResource
     * @throws \Throwable
     */
    public function store(StoreOrderRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data) {
            // Paso 1: Crear cabecera inicial de la orden de servicio
            $order = Order::create([
                'customer_name' => trim($data['customer_name']),
                'vehicle'       => trim($data['vehicle']),
                'notes'         => !empty($data['notes']) ? trim($data['notes']) : null,
                'status'        => 'open',
                'total'         => 0,
            ]);

            $total = 0;

            // Paso 2: Procesar cada partida, congelar precio y actualizar stock
            foreach ($data['items'] as $it) {
                $product  = Product::findOrFail($it['product_id']);
                $unit     = $product->price;
                $subtotal = $unit * $it['qty'];

                // Descontar inventario físico solo si no es mano de obra/servicio
                if (empty($product->is_service)) {
                    $product->decrement('stock', $it['qty']);
                }

                // Guardar la línea de la orden con precio congelado (Snapshot Pricing)
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity'   => $it['qty'],
                    'unit_price' => $unit,
                    'subtotal'   => $subtotal,
                ]);

                $total += $subtotal;
            }

            // Paso 3: Asignar el total consolidado a la orden
            $order->update(['total' => $total]);

            return new OrderResource($order->load('items.product'));
        });
    }

    // =========================================================================
    // SECCIÓN 3: CONSULTA INDIVIDUAL DE ORDEN Y DIAGNÓSTICO
    // =========================================================================

    /**
     * Obtiene el detalle completo de una orden específica por ID.
     *
     * @param  \App\Models\Order  $order
     * @return \App\Http\Resources\OrderResource
     */
    public function show(Order $order)
    {
        return new OrderResource($order->load('items.product'));
    }

    // =========================================================================
    // SECCIÓN 4: ACTUALIZACIÓN DE ESTADO OPERATIVO (FLUJO DE TALLER)
    // =========================================================================

    /**
     * Actualiza el estado del vehículo o datos del cliente.
     *
     * @param  \App\Http\Requests\UpdateOrderRequest  $request
     * @param  \App\Models\Order                     $order
     * @return \App\Http\Resources\OrderResource
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());
        return new OrderResource($order->load('items.product'));
    }

    // =========================================================================
    // SECCIÓN 5: CANCELACIÓN Y ELIMINACIÓN EN CASCADA
    // =========================================================================

    /**
     * // Función para eliminar una orden de servicio y sus partidas
     * 
     * Elimina el registro maestro. La base de datos elimina en cascada (ON DELETE CASCADE)
     * las partidas asociadas en la tabla 'order_items' manteniendo la integridad.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['deleted' => true]);
    }
}
