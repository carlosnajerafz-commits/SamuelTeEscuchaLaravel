<?php

namespace App\Services;

use App\Models\EventoComedor;
use App\Models\RegistroComedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ComedorService
{
    public function ensureCapacity(EventoComedor $event, int $people, ?int $except = null): void
    {
        $occupied = RegistroComedor::where('evento_id', $event->id)->where('estatus', '!=', 'cancelado')->when($except, fn ($query) => $query->where('id', '!=', $except))->sum('numero_personas');
        if ($event->cupo_maximo > 0 && $occupied + $people > $event->cupo_maximo) {
            throw ValidationException::withMessages(['numero_personas' => 'No hay cupo suficiente en este evento.']);
        }
    }

    public function reserve(array $data): RegistroComedor
    {
        return DB::transaction(function () use ($data) {
            $event = EventoComedor::whereKey($data['evento_id'])->lockForUpdate()->firstOrFail();
            if (! $event->activo || $event->fecha < today()->toDateString()) {
                throw ValidationException::withMessages(['evento_id' => 'El evento ya no está disponible.']);
            }
            $this->ensureCapacity($event, (int) $data['numero_personas']);

            return RegistroComedor::create([...$data, 'estatus' => 'pendiente']);
        });
    }
}
