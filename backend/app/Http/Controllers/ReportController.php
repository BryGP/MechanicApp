<?php

namespace App\Http\Controllers;

use App\Reports\ReportManager;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * ============================================================================
 * CLASE: ReportController
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Expone la API del motor de inteligencia de negocios y analítica del taller.
 * Conecta el frontend con el motor de descubrimiento de 'ReportManager', permitiendo:
 * 1. Listar el catálogo completo de reportes SQL auto-detectados en el backend.
 * 2. Ejecutar cualquier reporte en tiempo real devolviendo la data en JSON para tablas.
 * 3. Compilar los resultados en un documento PDF ejecutivo apaisado (Landscape A4)
 *    con fecha, hora y encabezados de auditoría listos para imprimir o descargar.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Arquitectura Plug-and-Play Desacoplada:
 *   El controlador jamás requiere registrar consultas o reportes de manera rígida.
 *   Cualquier reporte nuevo creado en 'app/Reports/' aparece automáticamente en el API.
 * - Motor de Exportación PDF con DomPDF:
 *   Combina la plantilla Blade corporativa ('pdf.report') con los metadatos dinámicos
 *   del reporte (título, categoría, total de registros, fecha de generación), 
 *   entregando un archivo PDF descargable con nomenclatura estandarizada.
 * - Manejo Resiliente de Excepciones:
 *   Atrapa solicitudes de reportes inexistentes o errores de sintaxis devolviendo 
 *   códigos HTTP 404/500 estructurados en JSON.
 *
 * ENDPOINTS ASOCIADOS (routes/api.php):
 * - GET /api/reports          -> index() (Catálogo de reportes)
 * - GET /api/reports/{id}/run -> run()   (Ejecutar y devolver JSON)
 * - GET /api/reports/{id}/pdf -> pdf()   (Descargar en PDF)
 * ============================================================================
 */
class ReportController extends Controller
{
    // =========================================================================
    // SECCIÓN 1: DESCUBRIMIENTO Y LISTADO DE REPORTES
    // =========================================================================

    /**
     * // Función para listar el catálogo de reportes disponibles en el sistema
     * 
     * Consulta el ReportManager para escanear y devolver la lista de todos 
     * los reportes analíticos activos, categorizados y listos para la barra lateral.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(ReportManager::all());
    }

    // =========================================================================
    // SECCIÓN 2: EJECUCIÓN DINÁMICA DE CONSULTAS (SALIDA JSON)
    // =========================================================================

    /**
     * // Función para ejecutar un reporte analítico por ID y devolver datos en JSON
     * 
     * Ejecuta la consulta SQL pura optimizada en MySQL para el reporte solicitado, 
     * devolviendo metadatos del reporte, conteo de filas y el arreglo de resultados.
     *
     * @param  string  $id  Identificador único del reporte (ej. 'stock_critico')
     * @return \Illuminate\Http\JsonResponse
     */
    public function run(string $id)
    {
        try {
            return response()->json(ReportManager::run($id));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    // =========================================================================
    // SECCIÓN 3: COMPILACIÓN Y DESCARGA EN PDF (FORMATO EJECUTIVO)
    // =========================================================================

    /**
     * // Función para compilar y descargar el reporte en formato PDF apaisado
     * 
     * Ejecuta la consulta analítica, inyecta la información en la vista Blade 
     * de PDF ('resources/views/pdf/report.blade.php') y compila un archivo PDF en 
     * orientación horizontal (Landscape A4) con fecha/hora de emisión.
     *
     * @param  string  $id  Identificador del reporte a exportar
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pdf(string $id)
    {
        try {
            $result = ReportManager::run($id);

            $pdf = Pdf::loadView('pdf.report', [
                'report'      => $result['report'],
                'data'        => $result['data'],
                'count'       => $result['count'],
                'generatedAt' => now()->format('d/m/Y H:i') . ' hrs',
            ])->setPaper('a4', 'landscape');

            $filename = 'reporte_' . $id . '_' . now()->format('Ymd_Hi') . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}