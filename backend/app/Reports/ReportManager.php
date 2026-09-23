<?php

namespace App\Reports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * ============================================================================
 * CLASE: ReportManager (Motor de Descubrimiento y Ejecución de Reportes)
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Funciona como el orquestador y motor central de Business Intelligence del taller.
 * Utiliza reflexión dinámica para escanear el directorio 'app/Reports', identificar 
 * todos los reportes analíticos disponibles sin necesidad de configurarlos manualmente 
 * en bases de datos o archivos de rutas, y despachar sus consultas SQL optimizadas en MySQL.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Arquitectura Zero-Config (Cero Configuración):
 *   Permite que cualquier desarrollador agregue un nuevo reporte al sistema con 
 *   solo colocar un archivo PHP en 'app/Reports/'. El motor lo detecta, lo categoriza 
 *   y lo expone instantáneamente en la interfaz y en el exportador PDF.
 * - Carga Ligera Diferida (Lazy Metadata Loading):
 *   El método 'all()' omite la sentencia SQL de la respuesta general para que el 
 *   menú y la barra lateral carguen en milisegundos sin transportar payloads pesados.
 * - Ejecución Directa de Alta Velocidad (DB::select):
 *   Corre consultas SQL complejas con funciones de agregación (DATEDIFF, ROUND, 
 *   GROUP BY, CASE) directamente en el motor relacional de MySQL sin la sobrecarga 
 *   de hidratación de modelos Eloquent.
 * ============================================================================
 */
class ReportManager
{
    // =========================================================================
    // SECCIÓN 1: DESCUBRIMIENTO AUTOMÁTICO DE REPORTES (AUTO-DISCOVERY)
    // =========================================================================

    /**
     * // Función para escanear el directorio y armar el catálogo dinámico de reportes
     * 
     * Inspecciona todos los archivos PHP en 'app/Reports/', ignora la propia clase 
     * gestora (ReportManager), verifica la existencia del método 'info()' y extrae 
     * los metadatos (id, title, category, icon, description, file).
     *
     * @return array Lista de reportes disponibles listos para el menú
     */
    public static function all(): array
    {
        $reports = [];
        $reportsPath = app_path('Reports');
        if (!File::exists($reportsPath)) {
            return [];
        }

        $files = File::files($reportsPath);

        foreach ($files as $file) {
            $filename = $file->getFilenameWithoutExtension();

            // Ignoramos el propio Manager y excepciones para evitar autoreferencias
            if ($filename === 'ReportManager' || $filename === 'ReportNotFoundException') {
                continue;
            }

            $className = "App\\Reports\\{$filename}";

            // Inspección reflexiva de la clase
            if (class_exists($className) && method_exists($className, 'info')) {
                $info = $className::info();
                // Omitimos la query en la lista general para que sea una respuesta rápida y ligera
                unset($info['query']);
                $info['file'] = $filename . '.php';
                $reports[] = $info;
            }
        }

        return $reports;
    }

    // =========================================================================
    // SECCIÓN 2: EJECUCIÓN DINÁMICA DE CONSULTAS SQL Y CONSOLIDACIÓN
    // =========================================================================

    /**
     * // Función para buscar y ejecutar la consulta SQL del reporte solicitado
     * 
     * Localiza la clase del reporte mediante su identificador ('id'), extrae 
     * la sentencia SQL pura y la ejecuta directamente en la base de datos MySQL.
     * Consolida los metadatos del reporte, el conteo de registros devueltos y la data.
     *
     * @param  string  $id  Identificador único del reporte (ej. 'autos_varados')
     * @return array       Arreglo con metadatos, conteo de filas y resultados
     * @throws ReportNotFoundException Si el reporte no existe o no se encuentra en el directorio
     */
    public static function run(string $id): array
    {
        $reportsPath = app_path('Reports');
        $files = File::files($reportsPath);

        foreach ($files as $file) {
            $filename = $file->getFilenameWithoutExtension();
            if ($filename === 'ReportManager' || $filename === 'ReportNotFoundException') continue;

            $className = "App\\Reports\\{$filename}";

            if (class_exists($className) && method_exists($className, 'info')) {
                $info = $className::info();
                if ($info['id'] === $id) {
                    // Ejecución directa de alta velocidad en MySQL
                    $results = DB::select($info['query']);
                    return [
                        'report' => [
                            'id'          => $info['id'],
                            'title'       => $info['title'],
                            'category'    => $info['category'],
                            'icon'        => $info['icon'] ?? '📊',
                            'description' => $info['description'] ?? '',
                            'query'       => trim($info['query']),
                        ],
                        'count'  => count($results),
                        'data'   => $results,
                    ];
                }
            }
        }

        throw new ReportNotFoundException("Reporte con ID '{$id}' no fue encontrado en app/Reports.");
    }
}
