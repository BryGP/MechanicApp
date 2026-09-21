<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

/**
 * ============================================================================
 * CLASE: ProductController
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Administra el catálogo integral de refacciones físicas y servicios de taller.
 * Coordina la creación, consulta, edición y eliminación de insumos, fluidos, 
 * piezas mecánicas y paquetes de mano de obra (diagnósticos, afinaciones, etc.).
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Soporte polimórfico híbrido para productos físicos vs. servicios ('is_service'):
 *   Si el elemento es un servicio de taller, se neutraliza el control de inventario
 *   (stock = 0, min_stock = 0), permitiendo cotizar mano de obra sin restricciones.
 * - Validación inteligente de SKU único mediante exclusión de ID propio durante 
 *   actualizaciones (PATCH/PUT), evitando falsos positivos de duplicidad.
 * - Protección de integridad referencial: el sistema impide la eliminación
 *   accidental de refacciones que ya pertenezcan a órdenes de servicio cerradas.
 *
 * MAPEO DE RUTAS (API Resource en routes/api.php):
 * - GET    /api/products      -> index()   (Listar inventario completo)
 * - POST   /api/products      -> store()   (Crear refacción o servicio)
 * - GET    /api/products/{id} -> show()    (Detalle individual)
 * - PUT    /api/products/{id} -> update()  (Actualización de campos)
 * - DELETE /api/products/{id} -> destroy() (Baja de catálogo)
 * ============================================================================
 */
class ProductController extends Controller
{
    // =========================================================================
    // SECCIÓN 1: CONSULTA Y LISTADO DE CATÁLOGO
    // =========================================================================

    /**
     * // Función para listar refacciones y servicios disponibles
     * 
     * Retorna la colección completa de productos y servicios ordenados 
     * alfabéticamente por su nombre comercial para la carga fluida en frontend.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return Product::orderBy('name')->get();
    }

    // =========================================================================
    // SECCIÓN 2: ALTA DE PRODUCTOS Y DISCRIMINACIÓN DE SERVICIOS
    // =========================================================================

    /**
     * // Función para registrar una nueva refacción física o servicio de taller
     * 
     * Evalúa las reglas de validación y discrimina si el registro corresponde a:
     * 1. Una refacción física: almacena existencias y umbral mínimo de reabastecimiento.
     * 2. Un servicio de mano de obra: fija existencias en 0 y marca la bandera 'is_service'.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:products,name',
            'sku'        => 'required|string|max:255|unique:products,sku',
            'price'      => 'required|numeric|min:0',
            'stock'      => 'nullable|integer|min:0',
            'min_stock'  => 'nullable|integer|min:0',
            'is_service' => 'nullable|boolean',
        ], [
            'name.unique'    => 'Ya existe una refacción o servicio registrado con este mismo nombre.',
            'name.required'  => 'El nombre de la refacción o servicio es obligatorio.',
            'sku.unique'     => 'El código o clave SKU ingresado ya está registrado en otro producto o servicio.',
            'sku.required'   => 'El código SKU es obligatorio.',
            'price.required' => 'El precio o tarifa es obligatorio.',
            'price.min'      => 'El precio o tarifa no puede ser un número negativo.',
        ]);

        // Discriminación automática: los servicios no controlan inventario
        if (!empty($data['is_service'])) {
            $data['stock'] = 0;
            $data['min_stock'] = 0;
            $data['is_service'] = true;
        } else {
            $data['stock'] = $data['stock'] ?? 0;
            $data['min_stock'] = $data['min_stock'] ?? 0;
            $data['is_service'] = false;
        }

        $product = Product::create($data);
        return response()->json($product, 201);
    }

    // =========================================================================
    // SECCIÓN 3: MODIFICACIÓN Y CONTROL DE PRECIOS/STOCK
    // =========================================================================

    /**
     * // Función para actualizar datos de una refacción o servicio existente
     * 
     * Permite actualización parcial (estilo PATCH/PUT). Valida la unicidad del SKU y
     * del Nombre ignorando el registro del propio producto que se está editando.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product       $product (Resuelto por Route Model Binding)
     * @return \App\Models\Product
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'       => "sometimes|string|max:255|unique:products,name,{$product->id}",
            'sku'        => "sometimes|string|max:255|unique:products,sku,{$product->id}",
            'price'      => 'sometimes|numeric|min:0',
            'stock'      => 'sometimes|integer|min:0',
            'min_stock'  => 'sometimes|integer|min:0',
            'is_service' => 'sometimes|boolean',
        ], [
            'name.unique' => 'Ya existe otra refacción o servicio registrado con este nombre.',
            'sku.unique'  => 'El código o SKU ingresado ya pertenece a otro producto o servicio.',
            'price.min'   => 'El precio o tarifa no puede ser un número negativo.',
        ]);

        $product->update($data);
        return $product;
    }

    // =========================================================================
    // SECCIÓN 4: CONSULTA INDIVIDUAL Y RESERVAS DE INTERFAZ
    // =========================================================================

    /**
     * // Función para consultar el detalle de un solo producto
     * 
     * Reservada para futuras vistas detalladas o fichas técnicas de producto.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * // Función para renderizado de formulario de edición (Server-Side)
     * 
     * Reservada para renderizado tradicional en caso de requerir Blade en el futuro.
     *
     * @param  \App\Models\Product  $product
     * @return void
     */
    public function edit(Product $product)
    {
        //
    }

    // =========================================================================
    // SECCIÓN 5: ELIMINACIÓN DE CATÁLOGO Y PROTECCIÓN REFERENCIAL
    // =========================================================================

    /**
     * // Función para eliminar definitivamente un producto o servicio
     * 
     * Ejecuta el borrado del registro. Si el producto ya fue utilizado en alguna
     * orden de servicio previa, la base de datos restringe el borrado (RESTRICT)
     * preservando la fidelidad histórica de las ventas.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(['deleted' => true]);
    }
}
