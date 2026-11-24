<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CodigoPostal;
use Illuminate\Http\JsonResponse;

class CodigoPostalController extends Controller
{
    /**
     * GET /api/v1/codigos-postales/{cp}
     *
     * Público. Devuelve las colonias y el municipio de un código postal.
     * Es el mismo dato que ya alimenta los formularios del sitio.
     */
    public function show(string $cp): JsonResponse
    {
        $registros = CodigoPostal::where('codigo_postal', $cp)
            ->orderBy('colonia')
            ->get(['colonia', 'municipio']);

        if ($registros->isEmpty()) {
            return response()->json([
                'ok' => false,
                'mensaje' => 'No se encontró el código postal en el catálogo.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'datos' => [
                'codigo_postal' => $cp,
                'municipio' => $registros->first()->municipio,
                'colonias' => $registros->pluck('colonia')->unique()->values(),
            ],
        ]);
    }
}
