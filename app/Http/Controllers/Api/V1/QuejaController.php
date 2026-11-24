<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuejaResource;
use App\Models\Queja;
use App\Services\EstatusSolicitudService;
use App\Services\SeguimientoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class QuejaController extends Controller
{
    /**
     * GET /api/v1/quejas?estatus=&tipo=&municipio=&por_pagina=
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'estatus' => ['nullable', Rule::in(config('samuel.modules.quejas.statuses'))],
            'tipo' => ['nullable', 'string', 'max:150'],
            'municipio' => ['nullable', 'string', 'max:150'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $quejas = Queja::query()
            ->when($request->filled('estatus'), fn ($query) => $query->where('estatus', $request->string('estatus')))
            ->when($request->filled('tipo'), fn ($query) => $query->where('tipo', $request->string('tipo')))
            ->when($request->filled('municipio'), fn ($query) => $query->where('municipio', $request->string('municipio')))
            ->orderByDesc('created_at')
            ->paginate($request->integer('por_pagina', 20));

        return QuejaResource::collection($quejas);
    }

    public function show(Queja $queja): QuejaResource
    {
        return new QuejaResource($queja);
    }

    public function actualizarEstatus(Request $request, Queja $queja, EstatusSolicitudService $estatus): JsonResponse
    {
        $datos = $request->validate(['estatus' => ['required', Rule::in(config('samuel.modules.quejas.statuses'))]]);
        $anterior = $queja->estatus;
        $queja = $estatus->update('quejas', $queja, $datos['estatus']);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Estatus actualizado correctamente.',
            'datos' => [
                'folio' => SeguimientoService::folio('quejas', $queja->id),
                'estatus_anterior' => $anterior,
                'estatus_actual' => $queja->estatus,
                'actualizado_por' => $request->user()->usuario,
            ],
        ]);
    }
}
