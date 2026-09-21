<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExpenseController;

/**
 * ============================================================================
 * ARCHIVO: routes/api.php (Enrutamiento Central de la API REST)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTE ARCHIVO?
 * Define todos los endpoints públicos y protegidos que conectan la interfaz Vue
 * del frontend con el motor de base de datos MySQL y la lógica de negocio en Laravel.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Convenciones RESTful Limpias mediante 'Route::apiResource':
 *   Genera automáticamente los estándares GET, POST, PUT y DELETE en una sola línea.
 * - Endpoints Polimórficos de Reportería:
 *   Las rutas '/reports/{id}/run' y '/reports/{id}/pdf' admiten cualquier ID de 
 *   reporte descubierto en disco sin necesidad de definir una ruta para cada reporte.
 * ============================================================================
 */

// =========================================================================
// SECCIÓN 1: SALUD DEL SISTEMA Y AUTENTICACIÓN
// =========================================================================

// Endpoint de verificación de usuario autenticado mediante Laravel Sanctum
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Endpoint de monitoreo de disponibilidad y uptime (Health Check)
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// =========================================================================
// SECCIÓN 2: INVENTARIO DE REFACCIONES Y CATÁLOGO DE SERVICIOS
// =========================================================================

// CRUD completo para refacciones físicas y servicios de mano de obra
Route::apiResource('products', ProductController::class);

// =========================================================================
// SECCIÓN 3: ÓRDENES DE SERVICIO Y DIAGNÓSTICO EN BAHÍAS
// =========================================================================

// Eliminación de órdenes protegida por PIN de Administrador
Route::delete('orders/{order}', [OrderController::class, 'destroy'])->middleware('admin.pin');

// Gestión del ciclo de vida de órdenes, cotizaciones y deducción de stock
Route::apiResource('orders', OrderController::class)->except(['destroy']);

// =========================================================================
// SECCIÓN 4: MOTOR DE BUSINESS INTELLIGENCE Y REPORTES ANALÍTICOS
// =========================================================================

// Listado de reportes descubiertos automáticamente en 'app/Reports/'
Route::get('reports',          [ReportController::class, 'index']);

// Ejecución directa de consultas analíticas con respuesta tabular JSON
Route::get('reports/{id}/run', [ReportController::class, 'run']);

// Compilación y exportación de reportes a PDF apaisado ejecutivo
Route::get('reports/{id}/pdf', [ReportController::class, 'pdf']);

// =========================================================================
// SECCIÓN 5: CONTABILIDAD, EGRESOS OPERATIVOS Y FLUJO DE CAJA
// =========================================================================

// Consulta cronológica de egresos del taller
Route::get('expenses',              [ExpenseController::class, 'index']);

// Registro de nuevos gastos categorizados con validación estricta
Route::post('expenses',             [ExpenseController::class, 'store']);

// Eliminación de partidas contables protegida por PIN de Administrador
Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->middleware('admin.pin');