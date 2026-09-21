<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

/**
 * ============================================================================
 * CLASE: ExpenseController
 * ============================================================================
 * 
 * ¿QUÉ HACE ESTA CLASE?
 * Controla todo el flujo operativo de gastos y egresos del taller mecánico. 
 * Permite registrar desembolsos categorizados (refacciones de urgencia, nóminas,
 * servicios básicos, renta, consumibles de taller y herramientas), listar el 
 * historial cronológico para balances financieros y eliminar partidas erróneas.
 *
 * LO MÁS NOVEDOSO / DESTACADO:
 * - Clasificación contable granular que alimenta directamente el módulo de
 *   reportes analíticos de "Fuga de Gastos".
 * - Doble criterio de ordenamiento cronológico ('expense_date' DESC y 'id' DESC)
 *   que garantiza fidelidad temporal en auditorías contables.
 * - Validación financiera estricta con importe mínimo positivo ($0.01) y 
 *   fechas normalizadas según el estándar ISO (Y-m-d).
 *
 * ENDPOINTS ASOCIADOS:
 * - GET    /api/expenses           -> index()   (Listar egresos)
 * - POST   /api/expenses           -> store()   (Crear egreso)
 * - DELETE /api/expenses/{expense} -> destroy() (Eliminar egreso)
 * ============================================================================
 */
class ExpenseController extends Controller
{
    // =========================================================================
    // SECCIÓN 1: CONSULTA DE CONTABILIDAD Y EGRESOS
    // =========================================================================

    /**
     * // Función para listar todos los egresos del taller
     * 
     * Retorna la totalidad de los gastos registrados ordenados de manera 
     * descendente por fecha contable y por identificador primario.
     * Alimenta la vista de Contabilidad y los balances de egresos del taller.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(
            Expense::orderBy('expense_date', 'desc')->orderBy('id', 'desc')->get()
        );
    }

    // =========================================================================
    // SECCIÓN 2: REGISTRO Y VALIDACIÓN CONTABLE
    // =========================================================================

    /**
     * // Función para registrar un nuevo gasto operativo
     * 
     * Valida de manera estricta los datos financieros entrantes:
     * - Concepto descriptivo y categoría obligatoria.
     * - Importe numérico no nulo y estrictamente mayor a cero.
     * - Método de pago y folio o referencia bancaria/factura opcionales.
     * - Fecha contable válida.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'concept'        => 'required|string|max:255',
            'category'       => 'required|string|max:100',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:50',
            'reference'      => 'nullable|string|max:100',
            'expense_date'   => 'required|date',
        ]);

        $expense = Expense::create($validated);

        return response()->json($expense, 201);
    }

    // =========================================================================
    // SECCIÓN 3: ELIMINACIÓN Y AUDITORÍA DE PARTIDAS
    // =========================================================================

    /**
     * // Función para eliminar un gasto o partida contable
     * 
     * Utiliza Route Model Binding de Laravel para resolver la instancia del gasto 
     * por ID y removerla de la base de datos de manera definitiva.
     *
     * @param  \App\Models\Expense  $expense
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(['message' => 'Egreso eliminado']);
    }
}