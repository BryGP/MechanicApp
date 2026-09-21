<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
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
 * - Separación de responsabilidades mediante Form Requests dedicados:
 *   (StoreProductRequest y UpdateProductRequest) para validaciones limpias.
 * - Transformación estandarizada mediante ProductResource (DTO):
 *   Garantiza un contrato JSON predecible y tipado para el frontend en Vue 3.
 * - Soporte polimórfico híbrido para productos físicos vs. servicios ('is_service').
 * - Protección de integridad referencial contra eliminación de piezas en órdenes previas.
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
     * Lista todas las refacciones y servicios disponibles transformados por ProductResource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return ProductResource::collection(Product::orderBy('name')->get());
    }

    // =========================================================================
    // SECCIÓN 2: ALTA DE PRODUCTOS Y DISCRIMINACIÓN DE SERVICIOS
    // =========================================================================

    /**
     * Registra una nueva refacción física o servicio de taller.
     *
     * @param  \App\Http\Requests\StoreProductRequest  $request
     * @return \App\Http\Resources\ProductResource
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

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
        return new ProductResource($product);
    }

    // =========================================================================
    // SECCIÓN 3: MODIFICACIÓN Y CONTROL DE PRECIOS/STOCK
    // =========================================================================

    /**
     * Actualiza datos de una refacción o servicio existente.
     *
     * @param  \App\Http\Requests\UpdateProductRequest  $request
     * @param  \App\Models\Product                     $product
     * @return \App\Http\Resources\ProductResource
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $product->update($data);
        return new ProductResource($product);
    }

    // =========================================================================
    // SECCIÓN 4: CONSULTA INDIVIDUAL
    // =========================================================================

    /**
     * Consulta el detalle de un solo producto o servicio por ID.
     *
     * @param  \App\Models\Product  $product
     * @return \App\Http\Resources\ProductResource
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
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
