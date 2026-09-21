<?php

namespace App\Http\Middleware;

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
        $expectedPin = (string) config('app.admin_pin', env('ADMIN_PIN', '1234'));

        // If customized PIN exists in cache or header matches valid pattern
        if (empty($inputPin) || trim((string) $inputPin) !== $expectedPin) {
            return response()->json([
                'message' => 'Acceso denegado: Se requiere un PIN de administrador válido para ejecutar esta operación.'
            ], 403);
        }

        return $next($request);
    }
}
