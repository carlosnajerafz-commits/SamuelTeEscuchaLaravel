<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use App\Services\CitaService;
use App\Services\EstatusSolicitudService;
use App\Services\SeguimientoService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class CitaController extends Controller
{
    /**
     * GET /api/v1/citas?estatus=&desde=&hasta=&por_pagina=
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'estatus' => ['nullable', Rule::in(config('samuel.modules.citas.statuses'))],
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $citas = Cita::query()
            ->when($request->filled('estatus'), fn ($query) => $query->where('estatus', $request->string('estatus')))
            ->when($request->filled('desde'), fn ($query) => $query->whereDate('fecha', '>=', $request->string('desde')))
            ->when($request->filled('hasta'), fn ($query) => $query->whereDate('fecha', '<=', $request->string('hasta')))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate($request->integer('por_pagina', 20));

        return CitaResource::collection($citas);
    }

    public function show(Cita $cita): CitaResource
    {
        return new CitaResource($cita);
    }

    /**
     * PATCH /api/v1/citas/{cita}/estatus — cuerpo: { "estatus": "aceptada" }
     */
    public function actualizarEstatus(Request $request, Cita $cita, EstatusSolicitudService $estatus): JsonResponse
    {
        $datos = $request->validate(['estatus' => ['required', Rule::in(config('samuel.modules.citas.statuses'))]]);
        $anterior = $cita->estatus;
        $cita = $estatus->update('citas', $cita, $datos['estatus']);

        return response()->json([
            'ok' => true,
            'mensaje' => 'Estatus actualizado correctamente.',
            'datos' => [
                'folio' => SeguimientoService::folio('citas', $cita->id),
                'estatus_anterior' => $anterior,
                'estatus_actual' => $cita->estatus,
                'actualizado_por' => $request->user()->usuario,
            ],
        ]);
    }

    /**
     * GET /api/v1/citas/disponibilidad/{fecha} — horarios libres de un martes,
     * respetando citas activas y bloqueos, sin revelar quién ocupa los demás.
     */
    public function disponibilidad(string $fecha, CitaService $citas): JsonResponse
    {
        $dia = Carbon::createFromFormat('!Y-m-d', $fecha);

        if (! $dia || $dia->format('Y-m-d') !== $fecha) {
            return response()->json(['ok' => false, 'mensaje' => 'La fecha no es válida.'], 422);
        }

        if (! $dia->isTuesday()) {
            return response()->json(['ok' => false, 'mensaje' => 'Las citas únicamente se agendan los martes.'], 422);
        }

        if (! $dia->isAfter(today())) {
            return response()->json(['ok' => false, 'mensaje' => 'Solo se puede agendar para un martes posterior a hoy.'], 422);
        }

        $libres = $citas->available($fecha);

        return response()->json([
            'ok' => true,
            'datos' => [
                'fecha' => $fecha,
                'horarios_libres' => $libres,
                'total_libres' => count($libres),
                'total_horarios' => count(config('samuel.slots')),
            ],
        ]);
    }
}
