<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Resources\ExpenseResource;
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
 * - Separación de validaciones contables en StoreExpenseRequest.
 * - Transformación estandarizada mediante ExpenseResource (DTO).
 * - Clasificación contable granular que alimenta directamente el módulo de
 *   reportes analíticos de "Fuga de Gastos".
 * - Doble criterio de ordenamiento cronológico ('expense_date' DESC y 'id' DESC).
 * - Validación financiera estricta con importe mínimo positivo ($0.01).
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
     * Retorna la totalidad de los gastos registrados ordenados cronológicamente
     * transformados por ExpenseResource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return ExpenseResource::collection(
            Expense::orderBy('expense_date', 'desc')->orderBy('id', 'desc')->get()
        );
    }

    // =========================================================================
    // SECCIÓN 2: REGISTRO Y VALIDACIÓN CONTABLE
    // =========================================================================

    /**
     * Registra un nuevo gasto operativo mediante StoreExpenseRequest validado.
     *
     * @param  \App\Http\Requests\StoreExpenseRequest  $request
     * @return \App\Http\Resources\ExpenseResource
     */
    public function store(StoreExpenseRequest $request)
    {
        $expense = Expense::create($request->validated());
        return new ExpenseResource($expense);
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