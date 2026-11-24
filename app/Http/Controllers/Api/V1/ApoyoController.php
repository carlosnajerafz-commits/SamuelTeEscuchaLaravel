<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApoyoResource;
use App\Http\Resources\RegistroApoyoResource;
use App\Models\Apoyo;
use App\Models\RegistroApoyo;
use App\Services\EstatusSolicitudService;
use App\Services\SeguimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ApoyoController extends Controller
{
    /**
     * GET /api/v1/apoyos — catálogo público de apoyos activos.
     */
    public function index(): AnonymousResourceCollection
    {
        return ApoyoResource::collection(Apoyo::where('activo', true)->orderByDesc('created_at')->get());
    }

    /**
     * GET /api/v1/registros-apoyo?estatus=&apoyo=&por_pagina=
     */
    public function registros(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'estatus' => ['nullable', Rule::in(config('samuel.modules.apoyos.statuses'))],
            'apoyo' => ['nullable', 'string', 'max:150'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $registros = RegistroApoyo::query()
            ->when($request->filled('estatus'), fn ($query) => $query->where('estatus', $request->string('estatus')))
            ->when($request->filled('apoyo'), fn ($query) => $query->where('apoyo', $request->string('apoyo')))
            ->orderByDesc('created_at')
            ->paginate($request->integer('por_pagina', 20));

        return RegistroApoyoResource::collection($registros);
    }

    public function actualizarEstatus(Request $request, RegistroApoyo $registro, EstatusSolicitudService $estatus): JsonResponse
    {
        $datos = $request->validate(['estatus' => ['required', Rule::in(config('samuel.modules.apoyos.statuses'))]]);
        $anterior = $registro->estatus;
        $registro = $estatus->update('apoyos', $registro, $datos['estatus']);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Estatus actualizado correctamente.',
            'datos' => [
                'folio' => SeguimientoService::folio('apoyos', $registro->id),
                'estatus_anterior' => $anterior,
                'estatus_actual' => $registro->estatus,
                'actualizado_por' => $request->user()->usuario,
            ],
        ]);
    }
}
