<?php

namespace App\Http\Controllers;

use App\Services\AdminPinService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class AdminPinController
 * 
 * Manages administrator PIN verification, real-time synchronization with client state,
 * and secure credential rotation.
 * 
 * @package App\Http\Controllers
 */
class AdminPinController extends Controller
{
    /**
     * Validates an entered PIN against the active system PIN.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        $isValid = AdminPinService::verify($request->input('pin'));

        if (!$isValid) {
            return response()->json([
                'valid'   => false,
                'message' => 'PIN de Administrador incorrecto.',
            ], 401);
        }

        return response()->json([
            'valid'   => true,
            'message' => 'PIN verificado correctamente.',
        ]);
    }

    /**
     * Updates the system administrator PIN after verifying the current PIN.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function change(Request $request): JsonResponse
    {
        $request->validate([
            'current_pin' => 'required|string',
            'new_pin'     => 'required|string|min:4|max:8',
        ]);

        $currentPin = (string) $request->input('current_pin');
        $newPin     = (string) $request->input('new_pin');

        if (!AdminPinService::verify($currentPin)) {
            return response()->json([
                'message' => 'El PIN actual es incorrecto.',
            ], 422);
        }

        AdminPinService::setPin($newPin);

        return response()->json([
            'success' => true,
            'message' => 'PIN de Administrador actualizado exitosamente en el servidor.',
        ]);
    }

    /**
     * Synchronizes a customized client PIN with the backend if the server is still on default.
     * Resolves client-server credential drift.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|string|min:4|max:8',
        ]);

        $clientPin = trim((string) $request->input('pin'));

        // Si el servidor aún tiene el PIN por defecto '1234' y el cliente provee uno personalizado, sincronizar
        if (AdminPinService::verify('1234') && $clientPin !== '1234') {
            AdminPinService::setPin($clientPin);
            return response()->json([
                'synced'  => true,
                'message' => 'PIN personalizado sincronizado con el servidor.',
            ]);
        }

        return response()->json([
            'synced'  => false,
            'current' => $currentServerPin === $clientPin,
        ]);
    }
}
