<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ============================================================================
 * CLASE: AppServiceProvider (Proveedor Raíz de Servicios de la Aplicación)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Es el punto neurálgico de configuración e inicialización de Laravel 11.
 * Coordina dos fases fundamentales del ciclo de vida del framework:
 * 1. register(): Enlaza servicios, repositorios y clases en el contenedor IoC.
 * 2. boot(): Inicializa comportamientos globales una vez que todos los demás 
 *    servicios han sido registrados (esquemas de DB, observers, directivas).
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Unificación Arquitectónica de Laravel 11:
 *   Reemplaza y unifica los múltiples proveedores de versiones previas 
 *   (RouteServiceProvider, AuthServiceProvider, EventServiceProvider), 
 *   centralizando el arranque en una estructura limpia, predecible y de alto rendimiento.
 * - Desactivación de Envoltorios de Recursos ('JsonResource::withoutWrapping()'):
 *   Garantiza que las respuestas JSON de la API REST mantengan estructuras planas
 *   compatibles con el cliente SPA sin envolver colecciones en claves 'data' arbitrarias.
 * ============================================================================
 */
class AppServiceProvider extends ServiceProvider
{
    // =========================================================================
    // SECCIÓN 1: REGISTRO DE SERVICIOS EN EL CONTENEDOR (REGISTER)
    // =========================================================================

    /**
     * // Función para registrar servicios y dependencias en el contenedor IoC
     * 
     * Se ejecuta de forma temprana antes de que la aplicación esté completamente 
     * disponible. Utilizado para enlazar interfaces a implementaciones concretas.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    // =========================================================================
    // SECCIÓN 2: ARRANQUE Y CONFIGURACIÓN GLOBAL DE LA APLICACIÓN (BOOT)
    // =========================================================================

    /**
     * // Función para inicializar configuraciones globales de arranque
     * 
     * Se ejecuta una vez que todos los proveedores han sido registrados.
     * Desactiva el envoltorio automático de recursos JSON para mantener compatibilidad
     * con el frontend en Vue 3.
     *
     * @return void
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
    }
}
