<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Emisión y revocación de tokens Bearer para empleados.
 */
class TokenController extends Controller
{
    /** Hash ficticio para que un usuario inexistente tarde lo mismo que uno real. */
    private const HASH_FICTICIO = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';

    /**
     * POST /api/v1/token
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'usuario' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:255'],
            'dispositivo' => ['nullable', 'string', 'max:100'],
        ]);

        $empleado = Empleado::whereRaw('LOWER(TRIM(usuario)) = LOWER(?)', [trim($datos['usuario'])])->first();
        $valido = password_verify($datos['password'], $empleado?->password_hash ?? self::HASH_FICTICIO);

        if (! $empleado || ! $valido) {
            throw ValidationException::withMessages(['usuario' => 'Usuario o contraseña incorrectos.']);
        }

        $token = $empleado->createToken($datos['dispositivo'] ?? 'api-cliente');

        return response()->json([
            'ok' => true,
            'datos' => [
                'token' => $token->plainTextToken,
                'tipo' => 'Bearer',
                'empleado' => $this->empleado($empleado),
            ],
        ], 201);
    }

    /**
     * GET /api/v1/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['ok' => true, 'datos' => $this->empleado($request->user())]);
    }

    /**
     * DELETE /api/v1/token — revoca el token con el que se hizo la petición.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['ok' => true, 'mensaje' => 'Token revocado correctamente.']);
    }

    /**
     * @return array{id: int, usuario: string, nombre: string}
     */
    private function empleado(Empleado $empleado): array
    {
        return ['id' => $empleado->id, 'usuario' => $empleado->usuario, 'nombre' => $empleado->nombre_completo];
    }
}
