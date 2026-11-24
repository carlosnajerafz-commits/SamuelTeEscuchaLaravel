<?php

namespace App\Services;

use App\Models\EventoComedor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Cambio de estatus de una solicitud, compartido por el panel y la API.
 *
 * Reactivar una cita vuelve a verificar que el horario siga libre y
 * reactivar un registro de comedor vuelve a verificar el cupo.
 */
class EstatusSolicitudService
{
    public function __construct(private CitaService $citas, private ComedorService $comedor) {}

    public function update(string $module, Model $record, string $estatus): Model
    {
        return DB::transaction(function () use ($module, $record, $estatus) {
            $record = $record->newQuery()->lockForUpdate()->findOrFail($record->getKey());

            if ($module === 'citas') {
                $this->citas->lockDay($record->fecha);
                if (in_array($estatus, ['solicitada', 'aceptada'])) {
                    $this->citas->ensureAvailable($record->fecha, $record->hora, $record->id);
                }
            }

            if ($module === 'comedor' && $record->evento_id && $estatus !== 'cancelado') {
                $event = EventoComedor::whereKey($record->evento_id)->lockForUpdate()->firstOrFail();
                $this->comedor->ensureCapacity($event, (int) $record->numero_personas, $record->id);
            }

            $record->update(['estatus' => $estatus]);

            return $record;
        });
    }
}
