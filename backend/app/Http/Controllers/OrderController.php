<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
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
 * - Transacciones Atómicas ACID (DB::transaction):
 *   Garantiza que la creación de la cabecera de orden, inserción de cada ítem, 
 *   congelamiento de precios y deducción de existencias en almacén ocurran de forma 
 *   atómica. Si una sola pieza no existe o falla la base de datos, se ejecuta un 
 *   Rollback absoluto, previniendo inconsistencias de stock.
 * - Snapshot Pricing (Congelamiento de Precios Históricos):
 *   Almacena el precio unitario del catálogo al momento de generar la orden. Si en 
 *   el futuro el precio de un aceite o balata sube, las órdenes pasadas conservan 
 *   su importe original para auditoría contable fidedigna.
 * - Eager Loading Relacional multinivel ('with('items.product')'):
 *   Evita el problema de consultas N+1 en el API, entregando la orden, sus 
 *   partidas y los detalles de las piezas en un único payload optimizado.
 * - Discriminación de Mano de Obra vs Refacciones:
 *   Deduce existencias de almacén únicamente si el ítem es una refacción física, 
 *   protegiendo los servicios de mano de obra de conteos negativos.
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
     * // Función para listar todas las órdenes de servicio del taller
     * 
     * Consulta las órdenes más recientes con precarga ansiosa (Eager Loading)
     * de sus partidas y el producto asociado para poblar el tablero Kanban y 
     * el módulo de Órdenes sin sobrecargar la base de datos con consultas N+1.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Order::with('items.product')->latest()->get();
    }

    // =========================================================================
    // SECCIÓN 2: CREACIÓN TRANSACCIONAL Y DEDUCCIÓN DE STOCK
    // =========================================================================

    /**
     * // Función para crear una orden de trabajo, calcular totales y descontar refacciones
     * 
     * Ejecuta una transacción atómica donde:
     * 1. Valida el cliente, vehículo y la existencia de al menos una partida.
     * 2. Inicializa la cabecera de la orden con estatus 'open'.
     * 3. Itera cada partida, toma el precio unitario vigente del producto, 
     *    descuenta el inventario físico (si no es servicio) y guarda el ítem.
     * 4. Suma los subtotales y consolida el total general de la orden.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Models\Order
     * @throws \Throwable En caso de error, la transacción revierte todo cambio.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name'        => 'nullable|string',
            'vehicle'              => 'nullable|string',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|integer|exists:products,id',
            'items.*.qty'          => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($data) {
            // Paso 1: Crear cabecera inicial de la orden de servicio
            $order = Order::create([
                'customer_name' => $data['customer_name'] ?? null,
                'vehicle'       => $data['vehicle'] ?? null,
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

            return $order->load('items');
        });
    }

    // =========================================================================
    // SECCIÓN 3: CONSULTA INDIVIDUAL DE ORDEN Y DIAGNÓSTICO
    // =========================================================================

    /**
     * // Función para obtener el detalle completo de una orden específica por ID
     * 
     * Retorna la orden resuelta por Route Model Binding con todas sus partidas.
     *
     * @param  \App\Models\Order  $order
     * @return \App\Models\Order
     */
    public function show(Order $order)
    {
        return $order->load('items');
    }

    // =========================================================================
    // SECCIÓN 4: ACTUALIZACIÓN DE ESTADO OPERATIVO (FLUJO DE TALLER)
    // =========================================================================

    /**
     * // Función para actualizar el estado del vehículo o datos del cliente
     * 
     * Permite transicionar la orden entre fases del taller:
     * - 'open'        : Recién ingresado, en espera de asignación de bahía.
     * - 'in_progress' : Mecánico trabajando actualmente en la unidad.
     * - 'done'        : Reparación concluida, listo para entrega.
     * - 'delivered'   : Entregado al cliente y liquidado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order         $order
     * @return \App\Models\Order
     */
    public function update(Request $request, Order $order)
    {
        $order->update($request->only('customer_name', 'vehicle', 'status'));
        return $order->load('items');
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
