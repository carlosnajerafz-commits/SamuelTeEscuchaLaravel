<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\SeguimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Consulta pública de seguimiento: exige folio y celular, y solo devuelve el estatus.
 */
class SeguimientoController extends Controller
{
    /**
     * POST /api/v1/seguimiento — cuerpo: { "folio": "CITA-000014", "celular": "5512345678" }
     */
    public function consultar(Request $request, SeguimientoService $seguimiento): JsonResponse
    {
        $datos = $request->validate([
            'folio' => ['required', 'string', 'regex:/^(CITA|QUEJA|APOYO|COMEDOR)-\d{1,6}$/i'],
            'celular' => ['required', 'string', 'regex:/^\d{10}$/'],
        ], [
            'folio.regex' => 'El folio debe tener el formato CITA-000001, QUEJA-000001, APOYO-000001 o COMEDOR-000001.',
            'celular.regex' => 'El celular debe tener exactamente 10 dígitos.',
        ]);

        $resultado = $seguimiento->consultar($datos['folio'], $datos['celular']);

        if (! $resultado) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No encontramos una solicitud con ese folio y ese número de celular. '
                    .'Verifica ambos datos o comunícate al '.SeguimientoService::TELEFONO.'.',
            ], 404);
        }

        return response()->json(['ok' => true, 'datos' => $resultado]);
    }
}
