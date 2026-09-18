<?php

namespace App\Reports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ReportManager
{
    /**
     * Escanea automáticamente la carpeta app/Reports y detecta
     * todos los reportes disponibles sin necesidad de configurarlos a mano.
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

            // Ignoramos el propio Manager
            if ($filename === 'ReportManager') {
                continue;
            }

            $className = "App\\Reports\\{$filename}";

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

    /**
     * Busca un reporte por su ID y ejecuta su consulta SQL en MySQL.
     */
    public static function run(string $id): array
    {
        $reportsPath = app_path('Reports');
        $files = File::files($reportsPath);

        foreach ($files as $file) {
            $filename = $file->getFilenameWithoutExtension();
            if ($filename === 'ReportManager') continue;

            $className = "App\\Reports\\{$filename}";

            if (class_exists($className) && method_exists($className, 'info')) {
                $info = $className::info();
                if ($info['id'] === $id) {
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

        throw new \Exception("Reporte con ID '{$id}' no fue encontrado en app/Reports.");
    }
}
