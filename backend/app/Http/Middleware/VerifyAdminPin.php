<?php

namespace App\Http\Middleware;

use App\Services\AdminPinService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VerifyAdminPin
 * 
 * Enforces Administrator PIN authorization for high-privilege/destructive operations
 * such as deleting work orders or removing accounting expenses.
 * Inspects the 'X-Admin-Pin' HTTP request header.
 * 
 * @package App\Http\Middleware
 */
class VerifyAdminPin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $inputPin = $request->header('X-Admin-Pin');

        if (empty($inputPin) || !AdminPinService::verify((string) $inputPin)) {
            return response()->json([
                'message' => 'Acceso denegado: Se requiere un PIN de administrador válido para ejecutar esta operación.'
            ], 403);
        }

        return $next($request);
    }
}
