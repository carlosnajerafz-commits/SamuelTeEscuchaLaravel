<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventoComedorResource;
use App\Http\Resources\RegistroComedorResource;
use App\Models\EventoComedor;
use App\Models\RegistroComedor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ComedorController extends Controller
{
    /**
     * GET /api/v1/comedor/eventos — próximos eventos activos con cupo disponible.
     */
    public function index(): AnonymousResourceCollection
    {
        $eventos = EventoComedor::query()
            ->where('activo', true)
            ->whereDate('fecha', '>=', today()->toDateString())
            ->withSum(['registros as personas_registradas' => fn ($query) => $query->where('estatus', '!=', 'cancelado')], 'numero_personas')
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        return EventoComedorResource::collection($eventos);
    }

    /**
     * GET /api/v1/comedor/registros?evento_id=&estatus=&por_pagina=
     */
    public function registros(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'evento_id' => ['nullable', 'integer'],
            'estatus' => ['nullable', Rule::in(config('samuel.modules.comedor.statuses'))],
            'por_pagina' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $registros = RegistroComedor::query()
            ->when($request->filled('evento_id'), fn ($query) => $query->where('evento_id', $request->integer('evento_id')))
            ->when($request->filled('estatus'), fn ($query) => $query->where('estatus', $request->string('estatus')))
            ->orderByDesc('created_at')
            ->paginate($request->integer('por_pagina', 20));

        return RegistroComedorResource::collection($registros);
    }
}
