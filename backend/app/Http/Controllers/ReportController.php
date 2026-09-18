<?php

namespace App\Http\Controllers;

use App\Reports\ReportManager;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Devuelve la lista de todos los reportes detectados en app/Reports
     */
    public function index()
    {
        return response()->json(ReportManager::all());
    }

    /**
     * Ejecuta el reporte seleccionado por su ID y devuelve los datos de MySQL
     */
    public function run(string $id)
    {
        try {
            $data = ReportManager::run($id);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }
}
